<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FigureController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\PublicCatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive']);

    // "products" se conserva como alias de "figures" por compatibilidad con la app móvil.
    foreach (['figures', 'products'] as $prefix) {
        Route::get("{$prefix}/top-selling", [FigureController::class, 'topSelling']);
        Route::apiResource($prefix, FigureController::class)->parameters([$prefix => 'figure'])->names(
            collect(['index', 'store', 'show', 'update', 'destroy'])
                ->mapWithKeys(fn ($m) => [$m => "{$prefix}.{$m}"])->all()
        );
        Route::patch("{$prefix}/{figure}/stock", [FigureController::class, 'updateStock']);
        Route::patch("{$prefix}/{figure}/toggle-active", [FigureController::class, 'toggleActive']);
        Route::post("{$prefix}/{figure}/sale", [FigureController::class, 'recordSale']);
    }

    Route::post('upload/image', [ImageUploadController::class, 'upload']);
    Route::post('upload/images', [ImageUploadController::class, 'uploadMultiple']);
});

Route::prefix('public')->group(function () {
    Route::get('catalog', [PublicCatalogController::class, 'catalog']);
    Route::get('figures', [PublicCatalogController::class, 'products']);
    Route::get('figures/{id}', [PublicCatalogController::class, 'product']);
    Route::get('products', [PublicCatalogController::class, 'products']);
    Route::get('products/{id}', [PublicCatalogController::class, 'product']);
});
