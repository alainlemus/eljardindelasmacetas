<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('funkomacetas')->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'data' => $category,
            'message' => 'Categoría creada correctamente',
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('funkomacetas');

        return response()->json([
            'data' => $category,
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($request->only(['name', 'description', 'is_active']));

        if ($request->has('name')) {
            $category->slug = \Illuminate\Support\Str::slug($request->name);
            $category->save();
        }

        return response()->json([
            'data' => $category,
            'message' => 'Categoría actualizada correctamente',
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->funkomacetas()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente',
        ]);
    }

    public function toggleActive(Category $category): JsonResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'data' => $category,
            'message' => $category->is_active ? 'Categoría activada' : 'Categoría desactivada',
        ]);
    }
}
