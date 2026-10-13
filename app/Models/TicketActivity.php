<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'description',
        'properties',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(int $ticketId, ?int $userId, string $action, string $description, ?array $properties = null): self
    {
        return static::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
