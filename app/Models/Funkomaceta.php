<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Funkomaceta extends Model
{
    use HasFactory;

    protected $table = 'funkomacetas';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'cost',
        'stock',
        'min_stock',
        'sku',
        'image',
        'images',
        'is_active',
        'is_featured',
        'sales_count',
        'category_id',
        'figure_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sales_count' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($funkomaceta) {
            if (empty($funkomaceta->slug)) {
                $funkomaceta->slug = Str::slug($funkomaceta->name);
            }
        });

        static::updating(function ($funkomaceta) {
            if ($funkomaceta->isDirty('name') && !$funkomaceta->isDirty('slug')) {
                $funkomaceta->slug = Str::slug($funkomaceta->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function figure(): BelongsTo
    {
        return $this->belongsTo(Figure::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeTopSelling($query, int $limit = 10)
    {
        return $query->orderByDesc('sales_count');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }
}
