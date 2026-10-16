<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Low => 'secondary',
            self::Medium => 'primary',
            self::High => 'warning',
            self::Urgent => 'danger',
        };
    }
}
