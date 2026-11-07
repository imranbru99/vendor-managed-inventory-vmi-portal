<x-filament-panels::page>
    @if ($tenantId === null)
        <x-filament::section>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                No tenant context is active. Platform administrators can manage the global module catalog under
                <strong>Modules</strong>. Assign <code>current_tenant_id</code> on your user to toggle tenant modules here.
            </p>
        </x-filament::section>
    @endif

    <div class="divide-y rounded-xl border border-gray-200 dark:border-gray-700">
        @foreach ($modules as $module)
            <div class="flex items-center justify-between gap-4 px-4 py-3" wire:key="module-{{ $module['slug'] }}">
                <div>
                    <p class="font-medium text-gray-950 dark:text-white">{{ $module['name'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $module['description'] }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $module['slug'] }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($module['is_required'])
                        <span class="text-xs font-medium text-teal-600">Required</span>
                    @endif
                    <x-filament::button
                        size="sm"
                        :color="$module['enabled'] ? 'danger' : 'primary'"
                        :disabled="$module['is_required'] || $tenantId === null"
                        wire:click="toggleModule('{{ $module['slug'] }}')"
                    >
                        {{ $module['enabled'] ? 'Disable' : 'Enable' }}
                    </x-filament::button>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
