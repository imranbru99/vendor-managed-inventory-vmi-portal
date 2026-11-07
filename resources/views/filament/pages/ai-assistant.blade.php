<x-filament-panels::page>
    @unless ($aiConfigured)
        <x-filament::section>
            <x-slot name="heading">AI is not configured</x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Set provider API keys in <code>.env</code> or run <code>php artisan ai-hub:configure</code>, then reload this page.
            </p>
        </x-filament::section>
    @endunless

    <form wire:submit="send" class="space-y-4">
        <textarea
            wire:model="prompt"
            rows="4"
            class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900"
            placeholder="Ask about inventory, proposals, or VMI policies…"
            @disabled(! $aiConfigured)
        ></textarea>

        <x-filament::button type="submit" :disabled="! $aiConfigured">
            Send
        </x-filament::button>
    </form>

    @if (filled($answer))
        <x-filament::section class="mt-6">
            <x-slot name="heading">Assistant</x-slot>
            <div class="prose max-w-none text-sm dark:prose-invert">
                {!! nl2br(e($answer)) !!}
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
