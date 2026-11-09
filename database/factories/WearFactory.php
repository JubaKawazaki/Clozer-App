<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wear>
 */
class WearFactory extends Factory
{
    public function definition(): array
    {
        return [
            'worn_on' => $this->faker->date(),
        ];
    }
}
