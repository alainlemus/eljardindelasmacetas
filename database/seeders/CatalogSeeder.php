<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Figure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Carga el catálogo del proveedor (Catalogo_Funko_Macetas.xlsx → data/catalogo.json).
 *
 * - "cost" es lo que cuesta comprar la figura al proveedor.
 * - El precio de venta es costo × MARKUP, redondeado hacia arriba a múltiplos de $5.
 * - Las figuras que el proveedor no trae con precio cuestan DEFAULT_COST ($85, confirmado por el
 *   proveedor): se cargan con ese costo y su precio de venta, y quedan activas. Al volver a
 *   correrlo también se les aplica a las ya cargadas que siguen sin costo ni precio.
 * - Las fotos salen del catálogo en PDF del proveedor, ya convertidas a WebP (data/images,
 *   mapeadas por SKU en data/catalogo_imagenes.json); se copian al disco público y solo se
 *   asignan a figuras que todavía no tienen foto.
 * - Es idempotente: vuelve a correrse sin duplicar (se identifica por SKU) y no pisa
 *   precio, stock ni fotos de figuras ya existentes.
 */
class CatalogSeeder extends Seeder
{
    private const MARKUP = 2.0;

    /** Costo de compra de las figuras sin precio en el catálogo del proveedor. */
    private const DEFAULT_COST = 85;

    public function run(): void
    {
        $items = json_decode(file_get_contents(__DIR__.'/data/catalogo.json'), true, flags: JSON_THROW_ON_ERROR);

        $photos = json_decode(file_get_contents(__DIR__.'/data/catalogo_imagenes.json'), true, flags: JSON_THROW_ON_ERROR);
        $disk = Storage::disk('public');
        $categories = [];
        $usedSlugs = Figure::pluck('slug', 'sku')->all();

        foreach ($items as $i => $item) {
            $sku = sprintf('FM-%04d', $i + 1);
            $category = $categories[$item['category']] ??= Category::firstOrCreate(
                ['slug' => Str::slug($item['category'])],
                ['name' => $item['category'], 'is_active' => true],
            );

            $cost = $item['cost'] ?? self::DEFAULT_COST;
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
                    'price' => $this->salePrice($cost),
                    'stock' => 0,
                    'is_active' => true,
                ]);
            } elseif ($figure->cost === null && (float) $figure->price <= 0) {
                // Ya cargada antes sin precio y sin capturar nada a mano: se le aplica el costo.
                $figure->fill(['cost' => $cost, 'price' => $this->salePrice($cost), 'is_active' => true]);
            }

            // Se asigna si no tiene foto, o se refresca si la foto es una de las que carga este seeder.
            $managed = ! $figure->image || str_starts_with($figure->image, 'funkomacetas/catalogo/');
            if ($managed && ! empty($photos[$sku])) {
                $paths = collect($photos[$sku])->map(function (string $file) use ($disk) {
                    $path = 'funkomacetas/catalogo/'.$file;
                    $disk->put($path, file_get_contents(__DIR__.'/data/images/'.$file), 'public');

                    return $path;
                });
                $figure->image = $paths->first();
                $figure->images = $paths->slice(1)->values()->all();
            }

            $figure->save();
        }
    }

    private function salePrice(float|int $cost): float
    {
        return ceil($cost * self::MARKUP / 5) * 5;
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
