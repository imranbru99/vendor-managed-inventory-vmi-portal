<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStep extends Model
{
    protected $fillable = [
        'workflow_definition_id',
        'name',
        'stage_order',
        'approver_role',
        'amount_threshold',
        'quantity_threshold',
        'is_auto_approve',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'amount_threshold' => 'decimal:4',
            'quantity_threshold' => 'decimal:4',
            'is_auto_approve' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class, 'workflow_step_id');
    }
}
