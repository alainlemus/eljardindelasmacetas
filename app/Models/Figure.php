<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Figure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'cost',
        'stock',
        'min_stock',
        'sales_count',
        'sku',
        'image',
        'images',
        'is_active',
        'is_featured',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'sales_count' => 'integer',
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Figure $figure) {
            if (empty($figure->slug)) {
                $figure->slug = static::uniqueSlug($figure->name, $figure->category?->name);
            }
        });

        static::updating(function (Figure $figure) {
            if ($figure->isDirty('name') && ! $figure->isDirty('slug')) {
                $figure->slug = static::uniqueSlug($figure->name, $figure->category?->name, $figure->id);
            }
        });
    }

    /**
     * Slug único: el nombre solo; si ya existe (p. ej. "Elsa" en Personajes y en Posket),
     * se le agrega la categoría y, como último recurso, un número.
     */
    public static function uniqueSlug(string $name, ?string $category = null, ?int $ignoreId = null): string
    {
        $taken = fn (string $slug): bool => static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        $slug = Str::slug($name);

        if ($taken($slug) && $category) {
            $slug = Str::slug($name.' '.$category);
        }

        $base = $slug;
        for ($n = 2; $taken($slug); $n++) {
            $slug = $base.'-'.$n;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeTopSelling(Builder $query): Builder
    {
        return $query->orderByDesc('sales_count');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$'.number_format($this->price, 2);
    }

    /** @return list<string> URLs de todas las fotos (principal primero). */
    public function getGalleryAttribute(): array
    {
        return collect([$this->image, ...($this->images ?? [])])
            ->filter()
            ->unique()
            ->map(fn (string $path) => Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::url($path))
            ->values()
            ->all();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->gallery[0] ?? null;
    }

    /** Enlace de WhatsApp: al número del negocio si está configurado, o selector de contacto. */
    public static function whatsappUrl(string $text, bool $toBusiness = false): string
    {
        $number = $toBusiness ? preg_replace('/\D+/', '', (string) config('services.whatsapp.number')) : '';

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }

    public function shareText(string $intro): string
    {
        return $intro."\n\n".$this->name."\nPrecio: ".$this->formatted_price."\n".route('catalog.product', $this->slug);
    }
}
