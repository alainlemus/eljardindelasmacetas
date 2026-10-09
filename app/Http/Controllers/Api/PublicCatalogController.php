<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FigureResource;
use App\Models\Category;
use App\Models\Figure;
use Illuminate\Http\JsonResponse;

class PublicCatalogController extends Controller
{
    public function catalog(): JsonResponse
    {
        $categories = Category::active()
            ->withCount(['figures' => function ($query) {
                $query->active();
            }])
            ->whereHas('figures', function ($query) {
                $query->active();
            })
            ->get();

        $featured = Figure::active()
            ->featured()
            ->with('category')
            ->limit(10)
            ->get();

        $totalProducts = Figure::active()->count();

        return response()->json([
            'data' => [
                'categories' => $categories,
                'featured' => FigureResource::collection($featured),
                'total_products' => $totalProducts,
            ],
        ]);
    }

    public function products(): JsonResponse
    {
        $figures = Figure::active()
            ->with('category')
            ->orderByRaw('stock > 0 desc')
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(
            $figures->through(fn ($product) => new FigureResource($product))
        );
    }

    public function product(int $id): JsonResponse
    {
        $product = Figure::active()
            ->with('category')
            ->findOrFail($id);

        return response()->json([
            'data' => new FigureResource($product),
        ]);
    }
}
