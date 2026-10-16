<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_number',
        'user_id',
        'assigned_to_id',
        'department_id',
        'category_id',
        'sla_plan_id',
        'subject',
        'description',
        'priority',
        'status',
        'response_due_at',
        'resolution_due_at',
        'first_responded_at',
        'resolved_at',
        'closed_at',
        'satisfaction_rating',
        'satisfaction_comment',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'satisfaction_rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function slaPlan(): BelongsTo
    {
        return $this->belongsTo(SlaPlan::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->latest();
    }

    public function statusEnum(): \App\Enums\TicketStatus
    {
        return \App\Enums\TicketStatus::tryFrom($this->status) ?? \App\Enums\TicketStatus::Open;
    }

    public function priorityEnum(): \App\Enums\TicketPriority
    {
        return \App\Enums\TicketPriority::tryFrom($this->priority) ?? \App\Enums\TicketPriority::Medium;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_PENDING], true);
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isBreached(): bool
    {
        if ($this->resolution_due_at === null) {
            return false;
        }

        if ($this->resolved_at) {
            return $this->resolved_at->isAfter($this->resolution_due_at);
        }

        return Carbon::now()->isAfter($this->resolution_due_at) && $this->isOpen();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_PENDING]);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Generate next ticket tracking number (e.g., HD-202610-0001).
     */
    public static function generateTicketNumber(): string
    {
        $prefix = 'HD-'.date('Ym').'-';

        try {
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $latest = static::where('ticket_number', 'LIKE', $prefix.'%')
                    ->orderByDesc('id')
                    ->value('ticket_number');

                if ($latest) {
                    $lastNumber = (int) substr($latest, strlen($prefix), 4);
                    $next = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
                } else {
                    $next = '0001';
                }

                $candidate = $prefix.$next;
                if (! static::where('ticket_number', $candidate)->exists()) {
                    return $candidate;
                }

                // High concurrency collision fallback
                $candidateWithEntropy = $prefix.$next.'-'.strtoupper(\Illuminate\Support\Str::random(3));
                if (! static::where('ticket_number', $candidateWithEntropy)->exists()) {
                    return $candidateWithEntropy;
                }
            }

            return $prefix.strtoupper(\Illuminate\Support\Str::random(6));
        } catch (\Throwable) {
            return $prefix.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        }
    }
}
