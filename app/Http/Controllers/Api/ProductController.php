<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Funkomaceta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Funkomaceta::with(['category', 'figure']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('featured')) {
            $query->where('is_featured', $request->featured);
        }

        if ($request->has('in_stock')) {
            $query->where('stock', '>', 0);
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'sku' => 'required|string|max:255|unique:funkomacetas,sku',
            'image' => 'nullable|string',
            'images' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'figure_id' => 'nullable|exists:figures,id',
        ]);

        $funkomaceta = Funkomaceta::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'cost' => $request->cost,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock ?? 5,
            'sku' => $request->sku,
            'image' => $request->image,
            'images' => $request->images ?? [],
            'is_active' => $request->is_active ?? true,
            'is_featured' => $request->is_featured ?? false,
            'category_id' => $request->category_id,
            'figure_id' => $request->figure_id,
        ]);

        $funkomaceta->load(['category', 'figure']);

        return response()->json([
            'data' => $funkomaceta,
            'message' => 'Producto creado correctamente',
        ], 201);
    }

    public function show(Funkomaceta $product): JsonResponse
    {
        $product->load(['category', 'figure']);

        return response()->json([
            'data' => $product,
        ]);
    }

    public function update(Request $request, Funkomaceta $product): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'sku' => 'sometimes|required|string|max:255|unique:funkomacetas,sku,' . $product->id,
            'image' => 'nullable|string',
            'images' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'figure_id' => 'nullable|exists:figures,id',
        ]);

        $product->update($request->only([
            'name', 'description', 'price', 'cost', 'stock', 'min_stock',
            'sku', 'image', 'images', 'is_active', 'is_featured',
            'category_id', 'figure_id'
        ]));

        if ($request->has('name')) {
            $product->slug = \Illuminate\Support\Str::slug($request->name);
            $product->save();
        }

        $product->load(['category', 'figure']);

        return response()->json([
            'data' => $product,
            'message' => 'Producto actualizado correctamente',
        ]);
    }

    public function destroy(Funkomaceta $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente',
        ]);
    }

    public function updateStock(Request $request, Funkomaceta $product): JsonResponse
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product->update([
            'stock' => $request->stock,
        ]);

        return response()->json([
            'data' => $product,
            'message' => 'Stock actualizado correctamente',
        ]);
    }
}
