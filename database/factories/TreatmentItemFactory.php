<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Treatment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TreatmentItem>
 */
class TreatmentItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'treatment_id' => Treatment::factory(),
            'sets'              => $this->faker->optional()->numberBetween(1, 10),
            'repetitions'       => $this->faker->optional()->numberBetween(1, 30),
            'duration_seconds'  => $this->faker->optional()->numberBetween(1, 300),
            'frequency_text'    => $this->faker->optional()->sentence(),
        ];
    }
    public function exercise(): static
    {
        return $this->state([
            'sets'             => $this->faker->numberBetween(1, 5),
            'repetitions'      => $this->faker->numberBetween(8, 20),
            'duration_seconds' => null,
            'frequency_text'   => '3x per week',
        ]);
    }

    /**
     * State: timed item (duration only, no sets/reps).
     */
    public function timed(): static
    {
        return $this->state([
            'sets'             => null,
            'repetitions'      => null,
            'duration_seconds' => $this->faker->numberBetween(30, 300),
            'frequency_text'   => 'Daily',
        ]);
    }
}
