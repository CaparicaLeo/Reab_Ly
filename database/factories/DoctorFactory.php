<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'crefito' => 'CREFITO-' . fake()->numerify('#####-F'),
            'specialty'=> fake()->randomElement(['Ortopedia', 'Neurologia', 'Cardiologia', 'Pediatria']),
            'user_id'   => User::factory(),
        ];
    }
}
