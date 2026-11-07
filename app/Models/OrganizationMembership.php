<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMembership extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'role_slug',
        'is_primary',
        'status',
        'invited_at',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
