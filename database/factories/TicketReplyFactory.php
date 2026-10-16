<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketReply>
 */
class TicketReplyFactory extends Factory
{
    protected $model = TicketReply::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraph(),
            'is_internal_note' => false,
        ];
    }

    public function internalNote(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal_note' => true,
        ]);
    }
}
