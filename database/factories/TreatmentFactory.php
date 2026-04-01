<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Doctor;
use App\Models\Patient;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Treatment>
 */
class TreatmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id'  => Doctor::factory(),
            'patient_id' => Patient::factory(),
            'title'      => fake()->sentence(3),
            'start_date' => fake()->date(),
            'end_date'   => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'status'     => fake()->randomElement(['ongoing', 'completed', 'cancelled']),
        ];
    }
}
