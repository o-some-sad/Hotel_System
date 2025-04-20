<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'actual_email' => $this->faker->unique()->safeEmail(),
            'image' => $this->faker->imageUrl(),
            'password' => bcrypt('password'),
            'nationalId' => $this->faker->unique()->numerify('##########'),
            'remember_token' => Str::random(10)
        ];
    }
}
