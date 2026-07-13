<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Funkomaceta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,jpg,png,gif,webp,heic,heif|max:10240',
        ]);

        $path = $request->file('image')->store('funkomacetas', 'public');

        return response()->json([
            'url' => $this->absoluteUrl($path),
            'path' => $path,
        ]);
    }

    public function uploadMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'required|file|mimes:jpeg,jpg,png,gif,webp,heic,heif|max:10240',
        ]);

        $paths = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('funkomacetas', 'public');
            $paths[] = $this->absoluteUrl($path);
        }

        return response()->json([
            'urls' => $paths,
        ]);
    }

    private function absoluteUrl(string $path): string
    {
        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}
