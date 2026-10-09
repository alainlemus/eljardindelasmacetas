<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Simplifica el modelo: Categoría -> Figura. La figura pasa a ser el producto
 * vendible (precio, stock, fotos) y se elimina la tabla funkomacetas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('figures', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('description');
            $table->decimal('cost', 10, 2)->nullable()->after('price');
            $table->integer('stock')->default(0)->after('cost');
            $table->integer('min_stock')->default(5)->after('stock');
            $table->unsignedInteger('sales_count')->default(0)->after('min_stock');
            $table->string('image')->nullable()->after('sku');
            $table->json('images')->nullable()->after('image');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->foreignId('category_id')->nullable()->after('is_featured')->constrained()->nullOnDelete();
            $table->index('sales_count');
        });

        if (Schema::hasTable('funkomacetas')) {
            $products = DB::table('funkomacetas')->get();

            // Las figuras "plantilla" referenciadas se reemplazan por el producto que las usa.
            $referenced = $products->pluck('figure_id')->filter()->unique()->all();
            if ($referenced) {
                DB::table('figures')->whereIn('id', $referenced)->delete();
            }

            foreach ($products as $p) {
                DB::table('figures')->insert([
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                    'price' => $p->price,
                    'cost' => $p->cost,
                    'stock' => $p->stock,
                    'min_stock' => $p->min_stock,
                    'sales_count' => $p->sales_count ?? 0,
                    'sku' => $p->sku,
                    'image' => $p->image,
                    'images' => $p->images,
                    'is_active' => $p->is_active,
                    'is_featured' => $p->is_featured,
                    'category_id' => $p->category_id,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ]);
            }

            Schema::drop('funkomacetas');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Esta migración no es reversible.');
    }
};
