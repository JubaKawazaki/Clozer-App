<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * La photo ne pointe pas vers un vrai fichier : les tests mines la DB, pas
     * le disque (chemins générés jamais réécrits, comme Closet). [PLACEHOLDER:
     * rembg] — thumb_path restera null jusqu'au service de coupe de fond.
     */
    public function definition(): array
    {
        return [
            'photo_path' => 'items/originals/'.$this->faker->unique()->uuid().'.webp',
            'name' => $this->faker->optional()->word(),
            'categories' => ['top'],
            'weather' => [],
            'styles' => ['casual'],
            'dress' => false,
            'archived' => false,
        ];
    }
}
