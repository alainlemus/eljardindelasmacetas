<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Figure;
use App\Models\Funkomaceta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FunkomacetaFactory extends Factory
{
    protected $model = Funkomaceta::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'name' => ucfirst($name) . ' Funkomaceta',
            'slug' => Str::slug($name) . '-funkomaceta',
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 15, 150),
            'cost' => fake()->randomFloat(2, 5, 80),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => 5,
            'sku' => 'FUNK-' . fake()->unique()->numerify('###-####'),
            'image' => fake()->imageUrl(400, 400, 'funko'),
            'images' => [],
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'category_id' => Category::factory(),
            'figure_id' => fake()->boolean(70) ? Figure::factory() : null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(1, 5),
            'min_stock' => 10,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
