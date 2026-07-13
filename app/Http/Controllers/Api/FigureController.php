<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Figure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FigureController extends Controller
{
    public function index(): JsonResponse
    {
        $figures = Figure::withCount('funkomacetas')->get();

        return response()->json([
            'data' => $figures,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:255|unique:figures,sku',
            'is_active' => 'boolean',
        ]);

        $figure = Figure::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description,
            'sku' => $request->sku,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'data' => $figure,
            'message' => 'Figura creada correctamente',
        ], 201);
    }

    public function show(Figure $figure): JsonResponse
    {
        $figure->loadCount('funkomacetas');

        return response()->json([
            'data' => $figure,
        ]);
    }

    public function update(Request $request, Figure $figure): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'sometimes|required|string|max:255|unique:figures,sku,' . $figure->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $figure->update($request->only(['name', 'description', 'sku', 'is_active']));

        if ($request->has('name')) {
            $figure->slug = \Illuminate\Support\Str::slug($figure->name);
            $figure->save();
        }

        return response()->json([
            'data' => $figure,
            'message' => 'Figura actualizada correctamente',
        ]);
    }

    public function toggleActive(Figure $figure): JsonResponse
    {
        $figure->update(['is_active' => !$figure->is_active]);

        return response()->json([
            'data' => $figure,
            'message' => $figure->is_active ? 'Figura activada' : 'Figura desactivada',
        ]);
    }

    public function destroy(Figure $figure): JsonResponse
    {
        if ($figure->funkomacetas()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la figura porque tiene productos asociados',
            ], 422);
        }

        $figure->delete();

        return response()->json([
            'message' => 'Figura eliminada correctamente',
        ]);
    }
}
