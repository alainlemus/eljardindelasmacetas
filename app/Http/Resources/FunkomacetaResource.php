<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FunkomacetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'cost' => $this->cost,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'category_id' => $this->category_id,
            'figure_id' => $this->figure_id,
            'image' => $this->absoluteUrl($this->image),
            'images' => collect($this->images ?? [])
                ->map(fn ($path) => $this->absoluteUrl($path))
                ->all(),
            'is_low_stock' => $this->is_low_stock,
            'sales_count' => $this->sales_count ?? 0,
            'formatted_price' => $this->formatted_price,
            'category' => $this->whenLoaded('category'),
            'figure' => $this->whenLoaded('figure'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}