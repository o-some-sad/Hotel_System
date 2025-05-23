<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;
use App\Models\Manager;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class FloorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $creatorTypes = [
            Admin::class,
            Manager::class,
        ];
        $createdByType = $this->faker->randomElement($creatorTypes);
        $createdById = $createdByType::inRandomOrder()->first()?->id ?? Admin::factory();

        return [
            'name' => $this->faker->unique()->word() . ' Floor',
            'number' => $this->faker->unique()->numberBetween(1, 100),
            'created_by_type' => $createdByType,
            'created_by_id' => $createdById,
        ];
    }
}
