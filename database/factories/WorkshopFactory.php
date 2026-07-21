<?php

namespace Database\Factories;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workshop>
 */
class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition(): array
    {
        return [
            'naziv' => fake()->sentence(3),
            'opis' => fake()->paragraph(),
            'dobna_skupina' => fake()->randomElement(['Dob 3-6', 'Dob 4-7', 'Dob 5-8']),
            'ikona' => fake()->randomElement(['calculator', 'book', 'flask', 'brush', 'people', 'graduation']),
            'boja' => fake()->randomElement(['amber', 'coral', 'purple', 'red']),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
