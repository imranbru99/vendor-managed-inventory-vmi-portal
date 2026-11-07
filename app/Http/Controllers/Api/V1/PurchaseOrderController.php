<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrganizationType;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 25), 100);
        $user = $request->user();

        $query = PurchaseOrder::query()->with(['lines.product:id,sku,name']);

        if ($user?->currentOrganization?->type === OrganizationType::Supplier) {
            $supplierOrgId = $user->current_organization_id;
            $query->whereHas('relationship', fn ($q) => $q->where('supplier_organization_id', $supplierOrgId));
        }

        $orders = $query->latest('id')->paginate($perPage);

        return response()->json($orders);
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $user = $request->user();

        if ($user?->currentOrganization?->type === OrganizationType::Supplier) {
            $purchaseOrder->load('relationship');
            if ((int) $purchaseOrder->relationship->supplier_organization_id !== (int) $user->current_organization_id) {
                abort(403, 'You do not have access to this purchase order.');
            }
        }

        $purchaseOrder->load(['lines.product:id,sku,name']);

        return response()->json(['data' => $purchaseOrder]);
    }
}
