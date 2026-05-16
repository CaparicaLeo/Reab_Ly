<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exercise>
 */
class ExerciseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'category'    => fake()->optional()->word(),
            'video_url'   => fake()->optional()->url(),
        ];
    }
}
