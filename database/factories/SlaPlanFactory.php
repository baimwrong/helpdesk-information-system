<?php

namespace Database\Factories;

use App\Models\SlaPlan;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlaPlan>
 */
class SlaPlanFactory extends Factory
{
    protected $model = SlaPlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' SLA',
            'priority' => fake()->randomElement([
                Ticket::PRIORITY_LOW,
                Ticket::PRIORITY_MEDIUM,
                Ticket::PRIORITY_HIGH,
                Ticket::PRIORITY_URGENT,
            ]),
            'response_time_hours' => fake()->numberBetween(1, 24),
            'resolution_time_hours' => fake()->numberBetween(8, 72),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
