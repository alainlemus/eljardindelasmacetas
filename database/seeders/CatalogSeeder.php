<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Figure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Carga el catálogo del proveedor (Catalogo_Funko_Macetas.xlsx → data/catalogo.json).
 *
 * - "cost" es lo que cuesta comprar la figura al proveedor.
 * - El precio de venta es costo × MARKUP, redondeado hacia arriba a múltiplos de $5.
 * - Las figuras sin costo quedan inactivas (ocultas en el catálogo) hasta que se les ponga precio.
 * - Es idempotente: vuelve a correrse sin duplicar (se identifica por SKU) y no pisa
 *   precio, stock ni fotos de figuras ya existentes.
 */
class CatalogSeeder extends Seeder
{
    private const MARKUP = 2.0;

    public function run(): void
    {
        $items = json_decode(file_get_contents(__DIR__.'/data/catalogo.json'), true, flags: JSON_THROW_ON_ERROR);

        $categories = [];
        $usedSlugs = Figure::pluck('slug', 'sku')->all();

        foreach ($items as $i => $item) {
            $sku = sprintf('FM-%04d', $i + 1);
            $category = $categories[$item['category']] ??= Category::firstOrCreate(
                ['slug' => Str::slug($item['category'])],
                ['name' => $item['category'], 'is_active' => true],
            );

            $cost = $item['cost'];
            $slug = $usedSlugs[$sku] ?? $this->uniqueSlug($item['name'], $item['category'], $usedSlugs);
            $usedSlugs[$sku] = $slug;

            $figure = Figure::firstOrNew(['sku' => $sku]);
            $isNew = ! $figure->exists;

            $figure->fill([
                'name' => $item['name'],
                'slug' => $slug,
                'category_id' => $category->id,
            ]);

            if ($isNew) {
                $figure->fill([
                    'cost' => $cost,
                    'price' => $cost ? ceil($cost * self::MARKUP / 5) * 5 : 0,
                    'stock' => 0,
                    'is_active' => $cost !== null,
                ]);
            }

            $figure->save();
        }
    }

    private function uniqueSlug(string $name, string $category, array $used): string
    {
        $slug = Str::slug($name);
        if (in_array($slug, $used, true)) {
            $slug = Str::slug($name.' '.$category);
        }

        $base = $slug;
        for ($n = 2; in_array($slug, $used, true); $n++) {
            $slug = $base.'-'.$n;
        }

        return $slug;
    }
}
