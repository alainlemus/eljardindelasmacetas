<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FigureController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicCatalogController;
use App\Http\Controllers\Api\ImageUploadController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive']);
    Route::apiResource('figures', FigureController::class);
    Route::patch('figures/{figure}/toggle-active', [FigureController::class, 'toggleActive']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::patch('products/{product}/stock', [ProductController::class, 'updateStock']);
    Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive']);
    Route::post('products/{product}/sale', [ProductController::class, 'recordSale']);
    Route::get('products/top-selling', [ProductController::class, 'topSelling']);

    Route::post('upload/image', [ImageUploadController::class, 'upload']);
    Route::post('upload/images', [ImageUploadController::class, 'uploadMultiple']);
});

Route::prefix('public')->group(function () {
    Route::get('catalog', [PublicCatalogController::class, 'catalog']);
    Route::get('products', [PublicCatalogController::class, 'products']);
    Route::get('products/{id}', [PublicCatalogController::class, 'product']);
});
