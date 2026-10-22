<?php

namespace App\Enums;

enum Stage: string
{
    case Todo = 'todo';
    case WaitingClarification = 'waiting_clarification';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'À faire',
            self::WaitingClarification => 'En attente de précision',
            self::InProgress => 'En cours',
            self::Done => 'Terminé',
            self::Abandoned => 'Abandonné',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $stage): array => [$stage->value => $stage->label()])
            ->all();
    }
}
