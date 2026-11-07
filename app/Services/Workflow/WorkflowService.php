<?php

namespace App\Services\Workflow;

use App\Enums\WorkflowStatus;
use App\Models\WorkflowApproval;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkflowService
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function start(WorkflowDefinition $definition, Model $entity): WorkflowInstance
    {
        $step = $definition->steps()->orderBy('stage_order')->first();

        if ($step === null) {
            throw new InvalidArgumentException('Workflow definition has no steps.');
        }

        return DB::transaction(function () use ($definition, $entity, $step): WorkflowInstance {
            $instance = WorkflowInstance::query()->create([
                'workflow_definition_id' => $definition->id,
                'tenant_id' => $definition->tenant_id,
                'entity_type' => $entity->getMorphClass(),
                'entity_id' => $entity->getKey(),
                'status' => WorkflowStatus::InProgress,
                'current_step_id' => $step->id,
                'started_at' => now(),
            ]);

            WorkflowApproval::query()->create([
                'workflow_instance_id' => $instance->id,
                'workflow_step_id' => $step->id,
                'status' => $step->is_auto_approve ? 'approved' : 'pending',
                'acted_at' => $step->is_auto_approve ? now() : null,
            ]);

            if ($step->is_auto_approve) {
                $this->advanceStep($instance->fresh(['definition.steps']));
            }

            $this->auditLogger->log('workflow.started', $instance, null, $instance->toArray());

            return $instance->fresh(['currentStep', 'approvals']);
        });
    }

    public function approve(WorkflowInstance $instance, ?string $notes = null, ?int $approverId = null): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $notes, $approverId): WorkflowInstance {
            $approval = $this->pendingApproval($instance);

            $old = $approval->toArray();
            $approval->update([
                'status' => 'approved',
                'notes' => $notes,
                'approver_id' => $approverId ?? Auth::id(),
                'acted_at' => now(),
            ]);

            $this->auditLogger->log('workflow.approved', $approval, $old, $approval->fresh()->toArray());

            $instance = $this->advanceStep($instance->fresh(['definition.steps', 'currentStep']));

            return $instance->fresh(['currentStep', 'approvals']);
        });
    }

    public function reject(WorkflowInstance $instance, ?string $notes = null, ?int $approverId = null): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $notes, $approverId): WorkflowInstance {
            $approval = $this->pendingApproval($instance);

            $old = $approval->toArray();
            $approval->update([
                'status' => 'rejected',
                'notes' => $notes,
                'approver_id' => $approverId ?? Auth::id(),
                'acted_at' => now(),
            ]);

            $instance->update([
                'status' => WorkflowStatus::Failed,
                'completed_at' => now(),
            ]);

            $this->auditLogger->log('workflow.rejected', $approval, $old, $approval->fresh()->toArray());

            return $instance->fresh(['currentStep', 'approvals']);
        });
    }

    protected function pendingApproval(WorkflowInstance $instance): WorkflowApproval
    {
        $approval = WorkflowApproval::query()
            ->where('workflow_instance_id', $instance->id)
            ->where('workflow_step_id', $instance->current_step_id)
            ->where('status', 'pending')
            ->first();

        if ($approval === null) {
            throw new InvalidArgumentException('No pending approval for the current workflow step.');
        }

        return $approval;
    }

    protected function advanceStep(WorkflowInstance $instance): WorkflowInstance
    {
        $steps = $instance->definition->steps()->orderBy('stage_order')->get();
        $currentOrder = $instance->currentStep?->stage_order ?? 0;
        $next = $steps->firstWhere('stage_order', '>', $currentOrder);

        if ($next === null) {
            $instance->update([
                'status' => WorkflowStatus::Completed,
                'completed_at' => now(),
                'current_step_id' => null,
            ]);

            return $instance;
        }

        $instance->update(['current_step_id' => $next->id]);

        WorkflowApproval::query()->create([
            'workflow_instance_id' => $instance->id,
            'workflow_step_id' => $next->id,
            'status' => $next->is_auto_approve ? 'approved' : 'pending',
            'acted_at' => $next->is_auto_approve ? now() : null,
        ]);

        if ($next->is_auto_approve) {
            return $this->advanceStep($instance->fresh(['definition.steps', 'currentStep']));
        }

        return $instance;
    }
}
