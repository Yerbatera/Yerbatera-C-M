<?php

namespace Database\Factories;

use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Turno>
 */
class TurnoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fecha' => fake()->date(),
            'turno' => fake()->randomElement(['mañana', 'tarde', 'noche']),
        ];
    }
}
