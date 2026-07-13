<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Figure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sku',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($figure) {
            if (empty($figure->slug)) {
                $figure->slug = Str::slug($figure->name);
            }
        });

        static::updating(function ($figure) {
            if ($figure->isDirty('name') && !$figure->isDirty('slug')) {
                $figure->slug = Str::slug($figure->name);
            }
        });
    }

    public function funkomacetas(): HasMany
    {
        return $this->hasMany(Funkomaceta::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
