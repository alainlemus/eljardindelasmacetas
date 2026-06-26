<?php

namespace Database\Factories;

use App\Models\Figure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FigureFactory extends Factory
{
    protected $model = Figure::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'sku' => 'FIG-' . fake()->unique()->numerify('###-####'),
        ];
    }
}
