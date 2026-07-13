<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FunkomacetaResource;
use App\Models\Funkomaceta;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class PublicCatalogController extends Controller
{
    public function catalog(): JsonResponse
    {
        $categories = Category::active()
            ->with(['funkomacetas' => function ($query) {
                $query->active()->inStock();
            }])
            ->whereHas('funkomacetas', function ($query) {
                $query->active()->inStock();
            })
            ->get();

        $featured = Funkomaceta::active()
            ->inStock()
            ->featured()
            ->with(['category', 'figure'])
            ->limit(10)
            ->get();

        $totalProducts = Funkomaceta::active()->inStock()->count();

        return response()->json([
            'data' => [
                'categories' => $categories,
                'featured' => FunkomacetaResource::collection($featured),
                'total_products' => $totalProducts,
            ],
        ]);
    }

    public function products(): JsonResponse
    {
        $products = Funkomaceta::active()
            ->inStock()
            ->with(['category', 'figure'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(
            $products->through(fn ($product) => new FunkomacetaResource($product))
        );
    }

    public function product(int $id): JsonResponse
    {
        $product = Funkomaceta::with(['category', 'figure'])
            ->findOrFail($id);

        if (!$product->is_active) {
            return response()->json([
                'message' => 'Producto no disponible',
            ], 404);
        }

        return response()->json([
            'data' => new FunkomacetaResource($product),
        ]);
    }
}
