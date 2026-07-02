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
        $name = $this->faker->unique()->words(3, true);
        return [
            'name' => ucfirst($name) . ' Funkomaceta',
            'slug' => Str::slug($name) . '-funkomaceta',
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 15, 150),
            'cost' => $this->faker->randomFloat(2, 5, 80),
            'stock' => $this->faker->numberBetween(0, 100),
            'min_stock' => 5,
            'sku' => 'FUNK-' . $this->faker->unique()->numerify('###-####'),
            'image' => $this->faker->imageUrl(400, 400, 'funko'),
            'images' => [],
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'category_id' => Category::factory(),
            'figure_id' => $this->faker->boolean(70) ? Figure::factory() : null,
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
            'stock' => $this->faker->numberBetween(1, 5),
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
