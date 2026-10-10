<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Figure;
use App\Support\OgImage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SeoController extends Controller
{
    public function robots(): Response
    {
        if (! config('seo.indexable')) {
            $body = "User-agent: *\nDisallow: /\n";
        } else {
            $body = implode("\n", [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
                'Disallow: /api/',
                'Disallow: /livewire',
                'Disallow: /catalog/share',
                'Disallow: /*?*search=',
                '',
                'Sitemap: '.route('sitemap'),
                '',
            ]);
        }

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }

    public function sitemap(): Response
    {
        if (! config('seo.indexable')) {
            abort(404);
        }

        $xml = Cache::remember('sitemap.xml', 600, function () {
            $urls = [['loc' => route('home'), 'lastmod' => Figure::active()->max('updated_at'), 'priority' => '1.0', 'freq' => 'daily']];

            foreach (Category::active()->whereHas('figures', fn ($q) => $q->active())->get() as $category) {
                $urls[] = ['loc' => route('home', ['category' => $category->slug]), 'lastmod' => $category->updated_at, 'priority' => '0.7', 'freq' => 'weekly'];
            }

            foreach (Figure::active()->orderBy('id')->get(['slug', 'updated_at']) as $figure) {
                $urls[] = ['loc' => route('catalog.product', $figure->slug), 'lastmod' => $figure->updated_at, 'priority' => '0.8', 'freq' => 'weekly'];
            }

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=600']);
    }

    /** Tarjeta 1200×630 (JPEG) para compartir una figura en WhatsApp, Facebook, X, etc. */
    public function ogImage(string $slug): BinaryFileResponse
    {
        $figure = Figure::active()->with('category')->where('slug', $slug)->firstOrFail();

        return response()->file(OgImage::path($figure), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400, s-maxage=604800',
        ]);
    }
}
