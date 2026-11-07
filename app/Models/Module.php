<?php

namespace App\Models;

use App\Enums\ModuleCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'version',
        'is_required',
        'is_global_active',
        'dependencies',
        'permissions',
        'settings_schema',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => ModuleCategory::class,
            'is_required' => 'boolean',
            'is_global_active' => 'boolean',
            'dependencies' => 'array',
            'permissions' => 'array',
            'settings_schema' => 'array',
        ];
    }

    public function tenantModules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }
}
