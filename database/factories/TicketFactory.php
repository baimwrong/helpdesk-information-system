<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => 'HD-'.date('Ym').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'assigned_to_id' => null,
            'department_id' => Department::factory(),
            'category_id' => Category::factory(),
            'sla_plan_id' => null,
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => Ticket::PRIORITY_MEDIUM,
            'status' => Ticket::STATUS_OPEN,
            'response_due_at' => now()->addHours(24),
            'resolution_due_at' => now()->addHours(48),
            'first_responded_at' => null,
            'resolved_at' => null,
            'closed_at' => null,
            'satisfaction_rating' => null,
            'satisfaction_comment' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_PENDING,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Ticket::PRIORITY_URGENT,
        ]);
    }

    public function assigned(?User $agent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to_id' => $agent?->id ?? User::factory()->agent(),
        ]);
    }
}
