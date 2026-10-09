<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FigureResource;
use App\Models\Figure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FigureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Figure::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        if ($request->has('in_stock')) {
            $query->where('stock', '>', 0);
        }

        $figures = $query->paginate($request->get('per_page', 15));

        return response()->json(
            $figures->through(fn ($figure) => new FigureResource($figure))
        );
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
            'sku' => 'required|string|max:255|unique:figures,sku',
            'image' => 'nullable|string',
            'images' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $figure = Figure::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'cost' => $request->cost,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock ?? 5,
            'sku' => $request->sku,
            'image' => $this->extractStoragePath($request->image),
            'images' => $this->extractStoragePaths($request->images ?? []),
            'is_active' => $request->is_active ?? true,
            'is_featured' => $request->is_featured ?? false,
            'category_id' => $request->category_id,
        ]);

        $figure->load('category');

        return response()->json([
            'data' => new FigureResource($figure),
            'message' => 'Figura creada correctamente',
        ], 201);
    }

    public function show(Figure $figure): JsonResponse
    {
        $figure->load('category');

        return response()->json([
            'data' => new FigureResource($figure),
        ]);
    }

    public function update(Request $request, Figure $figure): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'sku' => 'sometimes|required|string|max:255|unique:figures,sku,'.$figure->id,
            'image' => 'nullable|string',
            'images' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $figure->update($request->only([
            'name', 'description', 'price', 'cost', 'stock', 'min_stock',
            'sku', 'image', 'images', 'is_active', 'is_featured',
            'category_id',
        ]));

        if ($request->has('image')) {
            $figure->image = $this->extractStoragePath($request->image);
        }
        if ($request->has('images')) {
            $figure->images = $this->extractStoragePaths($request->images ?? []);
        }
        $figure->save();

        if ($request->has('name')) {
            $figure->slug = Str::slug($request->name);
            $figure->save();
        }

        $figure->load('category');

        return response()->json([
            'data' => new FigureResource($figure),
            'message' => 'Figura actualizada correctamente',
        ]);
    }

    public function destroy(Figure $figure): JsonResponse
    {
        $figure->delete();

        return response()->json([
            'message' => 'Figura eliminada correctamente',
        ]);
    }

    public function updateStock(Request $request, Figure $figure): JsonResponse
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $figure->update([
            'stock' => $request->stock,
        ]);

        return response()->json([
            'data' => new FigureResource($figure),
            'message' => 'Stock actualizado correctamente',
        ]);
    }

    public function toggleActive(Figure $figure): JsonResponse
    {
        $figure->update(['is_active' => ! $figure->is_active]);

        return response()->json([
            'data' => new FigureResource($figure),
            'message' => $figure->is_active ? 'Figura activada' : 'Figura desactivada',
        ]);
    }

    public function recordSale(Request $request, Figure $figure): JsonResponse
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = $request->quantity ?? 1;

        $figure->increment('sales_count', $quantity);

        return response()->json([
            'data' => new FigureResource($figure->fresh()),
            'message' => "Venta registrada: +{$quantity}",
        ]);
    }

    public function topSelling(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $figures = Figure::active()
            ->topSelling()
            ->with('category')
            ->limit(min((int) $limit, 50))
            ->get();

        return response()->json([
            'data' => FigureResource::collection($figures),
        ]);
    }

    private function extractStoragePath(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (preg_match('#/storage/(.+)$#', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    private function extractStoragePaths(array $values): array
    {
        return array_values(array_filter(array_map(
            fn ($v) => is_string($v) ? $this->extractStoragePath($v) : null,
            $values
        )));
    }
}
