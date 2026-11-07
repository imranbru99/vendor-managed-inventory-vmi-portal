<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        <x-filament::section>
            <x-slot name="heading">Database</x-slot>
            @if ($databaseOk)
                <p class="text-sm font-medium text-success-600">Connected</p>
            @else
                <p class="text-sm font-medium text-danger-600">Unavailable</p>
                @if ($databaseError)
                    <p class="mt-2 text-xs text-gray-500">{{ $databaseError }}</p>
                @endif
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Cache</x-slot>
            @if ($cacheOk)
                <p class="text-sm font-medium text-success-600">Read/write OK</p>
            @else
                <p class="text-sm font-medium text-danger-600">Failed</p>
                @if ($cacheError)
                    <p class="mt-2 text-xs text-gray-500">{{ $cacheError }}</p>
                @endif
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Queue</x-slot>
            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $this->queueDriverLabel() }}</p>
            <p class="mt-1 text-xs text-gray-500">
                {{ $this->queueIsAsync() ? 'Background workers recommended.' : 'Jobs run synchronously.' }}
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
