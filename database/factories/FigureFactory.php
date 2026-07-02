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
        $name = $this->faker->unique()->words(3, true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'sku' => 'FIG-' . $this->faker->unique()->numerify('###-####'),
        ];
    }
}
