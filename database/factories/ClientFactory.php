<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\Manager;
use App\Models\Receptionist;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    use HasFactory;
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
            Receptionist::class,
        ];
        $createdByType = $this->faker->randomElement($creatorTypes);
        $createdById = $createdByType::inRandomOrder()->first()?->id ?? Admin::factory();
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'image' => $this->faker->imageUrl(),
            'password' => bcrypt('password'),
            'nationalId' => $this->faker->unique()->numerify('##########'),
            'remember_token' => Str::random(10),
            'created_by_type' => $createdByType,
            'created_by_id' => $createdById,
            'country' => $this->faker->country(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'verified_at' => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}
