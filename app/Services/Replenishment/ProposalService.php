<?php

namespace App\Services\Replenishment;

use App\Enums\ProposalStatus;
use App\Models\BuyerSupplierRelationship;
use App\Models\ReplenishmentProposal;
use App\Models\ReplenishmentProposalLine;
use App\Models\ReplenishmentRecommendation;
use App\Models\SupplierProduct;
use App\Services\Audit\AuditLogger;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProposalService
{
    public function __construct(
        protected PurchaseOrderService $purchaseOrderService,
        protected AuditLogger $auditLogger,
    ) {}

    public function createFromRecommendations(
        BuyerSupplierRelationship $relationship,
        Collection|array $recommendations,
        ?string $proposalNumber = null,
    ): ReplenishmentProposal {
        $recommendations = collect($recommendations);

        return DB::transaction(function () use ($relationship, $recommendations, $proposalNumber): ReplenishmentProposal {
            $tenantId = (int) $recommendations->first()?->tenant_id;
            $proposalNumber ??= $this->nextProposalNumber($tenantId);

            $proposal = ReplenishmentProposal::query()->create([
                'tenant_id' => $tenantId,
                'relationship_id' => $relationship->id,
                'proposal_number' => $proposalNumber,
                'status' => ProposalStatus::Draft,
            ]);

            $totalQty = 0.0;
            $totalValue = 0.0;
            $currencyCode = 'USD';

            foreach ($recommendations as $recommendation) {
                if (! $recommendation instanceof ReplenishmentRecommendation) {
                    continue;
                }

                if ((float) $recommendation->recommended_qty <= 0) {
                    continue;
                }

                $supplierProduct = SupplierProduct::query()
                    ->where('relationship_id', $relationship->id)
                    ->where('product_id', $recommendation->product_id)
                    ->first();

                $unitPrice = (float) ($supplierProduct?->unit_price ?? 0);
                $currencyCode = $supplierProduct?->currency_code ?? $currencyCode;
                $qty = (float) $recommendation->recommended_qty;

                ReplenishmentProposalLine::query()->create([
                    'proposal_id' => $proposal->id,
                    'recommendation_id' => $recommendation->id,
                    'product_id' => $recommendation->product_id,
                    'warehouse_id' => $recommendation->warehouse_id,
                    'policy_id' => $recommendation->policy_id,
                    'recommended_qty' => $qty,
                    'proposed_qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_status' => 'pending',
                ]);

                $totalQty += $qty;
                $totalValue += $qty * $unitPrice;
            }

            $proposal->update([
                'total_qty' => $totalQty,
                'total_value' => $totalValue,
                'currency_code' => $currencyCode,
            ]);

            $this->auditLogger->log('proposal.created', $proposal, null, $proposal->toArray());

            return $proposal->fresh('lines');
        });
    }

    public function submit(ReplenishmentProposal $proposal): ReplenishmentProposal
    {
        if ($proposal->status !== ProposalStatus::Draft) {
            throw new InvalidArgumentException('Only draft proposals can be submitted.');
        }

        $old = $proposal->toArray();
        $proposal->update([
            'status' => ProposalStatus::PendingApproval,
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
        ]);

        $this->auditLogger->log('proposal.submitted', $proposal, $old, $proposal->fresh()->toArray());

        return $proposal->fresh();
    }

    public function approve(ReplenishmentProposal $proposal, ?string $notes = null): ReplenishmentProposal
    {
        if ($proposal->status !== ProposalStatus::PendingApproval) {
            throw new InvalidArgumentException('Proposal is not pending approval.');
        }

        return DB::transaction(function () use ($proposal, $notes): ReplenishmentProposal {
            $old = $proposal->toArray();

            foreach ($proposal->lines as $line) {
                $line->update([
                    'approved_qty' => $line->proposed_qty,
                    'line_status' => 'approved',
                ]);
            }

            $proposal->update([
                'status' => ProposalStatus::Approved,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            $purchaseOrder = $this->purchaseOrderService->generateFromProposal($proposal->fresh('lines'));

            $proposal->update(['status' => ProposalStatus::Converted]);

            $this->auditLogger->log('proposal.approved', $proposal, $old, [
                ...$proposal->fresh()->toArray(),
                'purchase_order_id' => $purchaseOrder->id,
            ]);

            return $proposal->fresh('lines');
        });
    }

    public function reject(ReplenishmentProposal $proposal, ?string $notes = null): ReplenishmentProposal
    {
        $old = $proposal->toArray();

        $proposal->update([
            'status' => ProposalStatus::Rejected,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $proposal->lines()->update(['line_status' => 'rejected']);

        $this->auditLogger->log('proposal.rejected', $proposal, $old, $proposal->fresh()->toArray());

        return $proposal->fresh();
    }

    public function requestRevision(ReplenishmentProposal $proposal, ?string $notes = null): ReplenishmentProposal
    {
        $old = $proposal->toArray();

        $proposal->update([
            'status' => ProposalStatus::Draft,
            'review_notes' => $notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $proposal->lines()->update(['line_status' => 'revision_requested']);

        $this->auditLogger->log('proposal.revision_requested', $proposal, $old, $proposal->fresh()->toArray());

        return $proposal->fresh();
    }

    protected function nextProposalNumber(int $tenantId): string
    {
        $sequence = ReplenishmentProposal::query()
            ->where('tenant_id', $tenantId)
            ->count() + 1;

        return sprintf('PROP-%s-%04d', now()->format('Ymd'), $sequence);
    }
}
