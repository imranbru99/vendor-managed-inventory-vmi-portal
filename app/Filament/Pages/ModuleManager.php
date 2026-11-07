<?php

namespace App\Filament\Pages;

use App\Models\Module;
use App\Models\TenantModule;
use App\Services\Modules\ModuleManager as TenantModuleManager;
use App\Services\Modules\ModuleRegistry;
use App\Support\TenantContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class ModuleManager extends Page
{
    protected string $view = 'filament.pages.module-manager';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Platform Administration';

    protected static ?string $navigationLabel = 'Tenant modules';

    protected static ?int $navigationSort = 20;

    /**
     * @var array<int, array{slug: string, name: string, description: string, is_required: bool, enabled: bool}>
     */
    public array $modules = [];

    public ?int $tenantId = null;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(ModuleRegistry $registry): void
    {
        $this->tenantId = TenantContext::getInstance()->getTenantId()
            ?? auth()->user()?->current_tenant_id;

        $this->refreshModules($registry);
    }

    public function toggleModule(string $slug, TenantModuleManager $moduleManager, ModuleRegistry $registry): void
    {
        if ($this->tenantId === null) {
            Notification::make()
                ->title('Select a tenant')
                ->body('Set your current tenant on your user profile to manage modules.')
                ->warning()
                ->send();

            return;
        }

        $row = collect($this->modules)->firstWhere('slug', $slug);

        if ($row === null || ($row['is_required'] ?? false)) {
            return;
        }

        try {
            if ($row['enabled']) {
                $moduleManager->disable($this->tenantId, $slug);
            } else {
                $moduleManager->enable($this->tenantId, $slug);
            }

            $this->refreshModules($registry);

            Notification::make()->title('Module updated')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Unable to update module')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function refreshModules(ModuleRegistry $registry): void
    {
        $enabledSlugs = $this->enabledSlugsForTenant();

        $this->modules = collect($registry->all())
            ->map(function (array $definition, string $slug) use ($enabledSlugs): array {
                $required = (bool) ($definition['is_required'] ?? false);

                return [
                    'slug' => $slug,
                    'name' => $definition['name'],
                    'description' => $definition['description'] ?? '',
                    'is_required' => $required,
                    'enabled' => $required || $enabledSlugs->contains($slug),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    protected function enabledSlugsForTenant(): Collection
    {
        if ($this->tenantId === null) {
            return collect();
        }

        return TenantModule::query()
            ->where('tenant_id', $this->tenantId)
            ->where('is_enabled', true)
            ->with('module:id,slug')
            ->get()
            ->pluck('module.slug');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncFromRegistry')
                ->label('Sync registry to database')
                ->visible(fn (): bool => (bool) auth()->user()?->is_platform_admin)
                ->action(function (ModuleRegistry $registry): void {
                    foreach ($registry->all() as $slug => $definition) {
                        Module::query()->updateOrCreate(
                            ['slug' => $slug],
                            [
                                'name' => $definition['name'],
                                'description' => $definition['description'] ?? null,
                                'icon' => $definition['icon'] ?? null,
                                'category' => $definition['category']->value ?? 'core',
                                'is_required' => $definition['is_required'] ?? false,
                                'dependencies' => $definition['dependencies'] ?? [],
                                'sort_order' => $definition['sort_order'] ?? 0,
                            ]
                        );
                    }

                    Notification::make()->title('Modules synchronized')->success()->send();
                }),
        ];
    }
}
