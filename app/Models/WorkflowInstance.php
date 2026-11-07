<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'workflow_definition_id',
        'entity_type',
        'entity_id',
        'status',
        'current_step_id',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => App\Enums\WorkflowStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class, 'workflow_instance_id');
    }
}
