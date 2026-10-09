<?php

namespace Database\Factories;

use App\Models\Category;
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
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 15, 150),
            'cost' => $this->faker->randomFloat(2, 5, 80),
            'stock' => $this->faker->numberBetween(1, 100),
            'min_stock' => 5,
            'sku' => 'FIG-'.$this->faker->unique()->numerify('###-####'),
            'image' => null,
            'images' => [],
            'is_active' => true,
            'is_featured' => false,
            'category_id' => Category::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
