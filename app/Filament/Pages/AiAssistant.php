<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use ImranDevBd\AiHub\Facades\AIHub;
use Throwable;

class AiAssistant extends Page
{
    protected string $view = 'filament.pages.ai-assistant';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Intelligence';

    protected static ?int $navigationSort = 1;

    public string $prompt = '';

    public ?string $answer = null;

    public bool $aiConfigured = false;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $default = config('ai-hub.default');
        $this->aiConfigured = filled($default) && filled(config("ai-hub.providers.{$default}.api_key"));
    }

    public function send(): void
    {
        if (! $this->aiConfigured) {
            Notification::make()
                ->title('AI is not configured')
                ->body('Configure AI Hub providers in your environment before using the assistant.')
                ->warning()
                ->send();

            return;
        }

        $prompt = trim($this->prompt);

        if ($prompt === '') {
            return;
        }

        try {
            $this->answer = (string) AIHub::prompt($prompt)->send()->text();
            Notification::make()->title('Response received')->success()->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('AI request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
