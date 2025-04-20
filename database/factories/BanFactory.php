<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;
use App\Models\Manager;
use App\Models\Receptionist;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bannedByTypes = [
            Admin::class,
            Manager::class,
        ];
        $bannedByType = $this->faker->randomElement($bannedByTypes);
        $bannedById = $bannedByType::inRandomOrder()->first()?->id ?? Admin::factory();

        return [
            'receptionist_id' => Receptionist::inRandomOrder()->first()?->id ?? Receptionist::factory(),
            'banned_by_type' => $bannedByType,
            'banned_by_id' => $bannedById,
        ];
    }
}
