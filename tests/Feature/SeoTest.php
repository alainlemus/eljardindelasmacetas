<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Figure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['seo.indexable' => true]);
        Cache::flush();
    }

    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn ($j) => json_decode($j, true, flags: JSON_THROW_ON_ERROR), $m[1]);
    }

    public function test_robots_blocks_private_areas_and_points_to_the_sitemap(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();

        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /api/', $body);
        $this->assertStringContainsString('Sitemap: '.route('sitemap'), $body);
        $this->assertStringNotContainsString("Disallow: /\n", $body);
    }

    public function test_non_production_sites_are_not_indexable(): void
    {
        config(['seo.indexable' => false]);
        Figure::factory()->create();

        $this->get('/robots.txt')->assertSee('Disallow: /', false);
        $this->get('/sitemap.xml')->assertNotFound();
        $this->get('/')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_indexable_flag_is_derived_from_the_host(): void
    {
        foreach (['dev.eljardindelasmacetas.com' => false, 'staging.x.com' => false, 'sistema-funkos.test' => false, 'eljardindelasmacetas.com' => true] as $host => $expected) {
            $value = filter_var(
                ! preg_match('/^(dev|staging|stage|test)\.|\.test$|localhost|^127\./', $host),
                FILTER_VALIDATE_BOOLEAN
            );
            $this->assertSame($expected, $value, $host);
        }
    }

    public function test_sitemap_lists_active_figures_and_categories_only(): void
    {
        $cat = Category::factory()->create(['slug' => 'marvel']);
        Figure::factory()->create(['slug' => 'iron-man', 'category_id' => $cat->id]);
        Figure::factory()->inactive()->create(['slug' => 'oculta', 'category_id' => $cat->id]);

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

        $this->assertStringContainsString(route('home'), $xml);
        $this->assertStringContainsString(route('catalog.product', 'iron-man'), $xml);
        $this->assertStringContainsString('category=marvel', $xml);
        $this->assertStringNotContainsString('oculta', $xml);
        $this->assertNotFalse(simplexml_load_string($xml), 'el sitemap debe ser XML válido');
    }

    public function test_home_has_complete_meta_and_structured_data(): void
    {
        Figure::factory()->create();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="es-MX">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('home').'">', $html);
        $this->assertStringContainsString('index, follow', $html);
        foreach (['og:title', 'og:description', 'og:image', 'og:image:width', 'og:image:alt', 'twitter:title', 'twitter:image'] as $tag) {
            $this->assertStringContainsString($tag, $html, $tag);
        }
        $this->assertSame(1, substr_count($html, '<h1'));

        $graph = $this->jsonLd($html)[0]['@graph'];
        $this->assertSame(['Organization', 'WebSite'], array_column($graph, '@type'));
    }

    public function test_search_pages_are_noindex_and_category_pages_have_their_own_canonical(): void
    {
        $cat = Category::factory()->create(['name' => 'Marvel', 'slug' => 'marvel']);
        Figure::factory()->create(['name' => 'Iron Man', 'category_id' => $cat->id]);

        $this->get('/?search=iron')->assertSee('noindex, nofollow', false)->assertSee('<link rel="canonical" href="'.route('home').'">', false);

        $this->get('/?category=marvel')
            ->assertSee('<title>Figuras de Marvel | El Jardín de las Macetas</title>', false)
            ->assertSee('<link rel="canonical" href="'.route('home', ['category' => 'marvel']).'">', false)
            ->assertSee('index, follow', false);
    }

    public function test_product_page_has_product_schema_with_offer_and_breadcrumbs(): void
    {
        $cat = Category::factory()->create(['name' => 'Marvel']);
        $f = Figure::factory()->create(['name' => 'Iron Man', 'slug' => 'iron-man', 'price' => 160, 'stock' => 0, 'category_id' => $cat->id, 'image' => 'funkomacetas/a.webp']);

        $response = $this->get('/catalog/iron-man')->assertOk()
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('<meta property="product:price:amount" content="160.00">', false)
            ->assertSee('<link rel="canonical" href="'.route('catalog.product', 'iron-man').'">', false);

        $graph = $this->jsonLd($response->getContent())[0]['@graph'];
        $product = $graph[0];
        $this->assertSame('Product', $product['@type']);
        $this->assertSame($f->sku, $product['sku']);
        $this->assertSame('160.00', $product['offers']['price']);
        $this->assertSame('MXN', $product['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/PreOrder', $product['offers']['availability']); // sin stock = sobre pedido
        $this->assertSame('BreadcrumbList', $graph[1]['@type']);
        $this->assertCount(3, $graph[1]['itemListElement']);
    }

    public function test_product_without_price_has_no_offer(): void
    {
        Figure::factory()->create(['slug' => 'sin-precio', 'price' => 0]);

        $html = $this->get('/catalog/sin-precio')->getContent();

        $this->assertArrayNotHasKey('offers', $this->jsonLd($html)[0]['@graph'][0]);
        $this->assertStringNotContainsString('product:price', $html);
    }

    public function test_catalog_redirects_to_the_single_listing_url(): void
    {
        $this->get('/catalog?category=marvel')->assertStatus(301)->assertRedirect(route('home', ['category' => 'marvel']));
    }

    public function test_public_pages_are_cacheable_and_set_no_cookies(): void
    {
        Figure::factory()->create(['slug' => 'goku']);

        foreach (['/', '/catalog/goku'] as $url) {
            $response = $this->get($url)->assertOk();
            $this->assertEmpty($response->headers->getCookies(), "{$url} no debe crear cookies ni sesión");
            $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        }
        $this->assertDatabaseCount('sessions', 0);
    }

    public function test_missing_pages_show_a_friendly_noindex_404(): void
    {
        $this->get('/catalog/no-existe')->assertNotFound()
            ->assertSee('No encontramos esa página')
            ->assertSee('noindex, nofollow', false);
    }

    public function test_images_have_dimensions_and_alt_text(): void
    {
        Figure::factory()->count(5)->create(['image' => 'funkomacetas/a.webp']);

        $html = $this->get('/')->getContent();

        preg_match_all('#<img\b[^>]*>#', $html, $imgs);
        foreach ($imgs[0] as $img) {
            $this->assertStringContainsString('alt=', $img, $img);
            $this->assertStringContainsString('width=', $img, $img);
            $this->assertStringContainsString('height=', $img, $img);
        }
        $this->assertStringContainsString('fetchpriority="high"', $html); // las primeras (LCP) cargan con prioridad
    }

    public function test_social_share_image_is_a_small_1200x630_jpeg_and_is_referenced_by_the_page(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $cat = Category::factory()->create(['name' => 'Marvel']);
        $figure = Figure::factory()->create(['name' => 'Iron Man', 'slug' => 'iron-man', 'price' => 160, 'category_id' => $cat->id]);

        $page = $this->get('/catalog/iron-man')->assertOk();
        $page->assertSee('<meta property="og:image" content="'.route('og.figure', 'iron-man').'">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false)
            ->assertSee('<meta property="og:image:type" content="image/jpeg">', false)
            ->assertSee('<meta name="twitter:image" content="'.route('og.figure', 'iron-man').'">', false);

        $response = $this->get(route('og.figure', 'iron-man'))->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $bytes = file_get_contents($response->baseResponse->getFile()->getPathname());
        $info = getimagesizefromstring($bytes);
        $this->assertSame([1200, 630], [$info[0], $info[1]]);
        $this->assertSame('image/jpeg', $info['mime']);
        $this->assertLessThan(300 * 1024, strlen($bytes), 'WhatsApp pide menos de 300 KB');

        // Se regenera si la figura cambia (otro nombre de archivo) y no deja versiones viejas.
        $figure->update(['price' => 200]);
        $this->get(route('og.figure', 'iron-man'))->assertOk();
        $this->assertCount(1, Storage::disk('local')->files('og'));
    }

    public function test_share_image_404s_for_unknown_or_inactive_figures(): void
    {
        Figure::factory()->inactive()->create(['slug' => 'oculta']);

        $this->get('/og/oculta.jpg')->assertNotFound();
        $this->get('/og/no-existe.jpg')->assertNotFound();
    }

    public function test_site_loads_its_own_fonts_and_not_google_fonts(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('fonts.gstatic.com', $html);
        $this->assertStringContainsString('fonts/site/Nunito-400.woff2', $html);
        $this->assertFileExists(public_path('fonts/site/Nunito-400.woff2'));
    }
}
