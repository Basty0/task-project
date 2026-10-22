<?php

namespace App\Enums;

enum Stage: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'À faire',
            self::InProgress => 'En cours',
            self::Done => 'Terminé',
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
