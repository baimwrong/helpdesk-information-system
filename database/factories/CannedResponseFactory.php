<?php

namespace Database\Factories;

use App\Models\CannedResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CannedResponse>
 */
class CannedResponseFactory extends Factory
{
    protected $model = CannedResponse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => null,
            'title' => fake()->sentence(3),
            'shortcut' => '!'.fake()->unique()->slug(1),
            'content' => fake()->paragraph(),
            'is_shared' => false,
        ];
    }

    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => true,
        ]);
    }
}
