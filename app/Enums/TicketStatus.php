<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Pending => 'Pending',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::InProgress => 'primary',
            self::Pending => 'warning',
            self::Resolved => 'success',
            self::Closed => 'secondary',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Open, self::InProgress, self::Pending], true);
    }

    public function isResolved(): bool
    {
        return $this === self::Resolved;
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }
}
