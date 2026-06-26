<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Figure;
use App\Models\Funkomaceta;
use Illuminate\Database\Seeder;

class FunkomacetaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Marvel', 'description' => 'Personajes del universo Marvel'],
            ['name' => 'DC Comics', 'description' => 'Personajes del universo DC'],
            ['name' => 'Anime', 'description' => 'Personajes de anime y manga'],
            ['name' => 'Películas', 'description' => 'Personajes de películas populares'],
            ['name' => 'Videojuegos', 'description' => 'Personajes de videojuegos'],
            ['name' => 'Disney', 'description' => 'Personajes de Disney'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $figures = [
            ['name' => 'Iron Man', 'sku' => 'FIG-MARVEL-001'],
            ['name' => 'Spider-Man', 'sku' => 'FIG-MARVEL-002'],
            ['name' => 'Batman', 'sku' => 'FIG-DC-001'],
            ['name' => 'Superman', 'sku' => 'FIG-DC-002'],
            ['name' => 'Goku', 'sku' => 'FIG-ANIME-001'],
            ['name' => 'Naruto', 'sku' => 'FIG-ANIME-002'],
            ['name' => 'Harry Potter', 'sku' => 'FIG-MOVIE-001'],
            ['name' => 'Mario Bros', 'sku' => 'FIG-GAME-001'],
        ];

        foreach ($figures as $figure) {
            Figure::create($figure);
        }

        Funkomaceta::factory()->count(30)->create();
    }
}
