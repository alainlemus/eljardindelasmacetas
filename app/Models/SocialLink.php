<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * WhatsApp y redes sociales del negocio, administrados desde el panel.
 * El primer WhatsApp activo es el número al que llegan los pedidos.
 */
class SocialLink extends Model
{
    public const PLATFORMS = [
        'whatsapp' => 'WhatsApp',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'x' => 'X (Twitter)',
        'other' => 'Otro enlace',
    ];

    private const CACHE_KEY = 'social_links.active';

    protected $fillable = [
        'platform',
        'label',
        'url',
        'phone',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (SocialLink $link) {
            if ($link->platform === 'whatsapp') {
                $link->phone = preg_replace('/\D+/', '', (string) $link->phone);
                $link->url = null;
            } else {
                $link->phone = null;
            }
        });

        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /** Para cambios masivos que no disparan eventos del modelo (p. ej. reordenar la tabla). */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return Collection<int, SocialLink> Enlaces activos, en el orden del panel. */
    public static function active(): Collection
    {
        // Se guardan arreglos, no modelos: Laravel no deserializa objetos del caché
        // (cache.serializable_classes), así que se rehidratan al leer.
        $rows = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (SocialLink $link) => $link->getAttributes())
            ->all());

        return static::hydrate(is_array($rows) ? $rows : [])->toBase();
    }

    /** Número (solo dígitos) al que llegan los pedidos, o null si no hay WhatsApp activo. */
    public static function whatsappNumber(): ?string
    {
        return static::active()->firstWhere('platform', 'whatsapp')?->phone ?: null;
    }

    public function getHrefAttribute(): ?string
    {
        return $this->platform === 'whatsapp'
            ? ($this->phone ? 'https://wa.me/'.$this->phone : null)
            : $this->url;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->label ?: (self::PLATFORMS[$this->platform] ?? $this->platform);
    }
}
