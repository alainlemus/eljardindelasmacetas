<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Figure;
use App\Models\User;
use App\Support\ImageOptimizer;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_share_route_is_not_shadowed_by_product_route(): void
    {
        $this->get('/catalog/share')->assertOk();
    }

    public function test_public_api_hides_cost_and_inactive_products(): void
    {
        $category = Category::factory()->create();
        $active = Figure::factory()->create(['category_id' => $category->id, 'is_active' => true, 'stock' => 3]);
        $inactive = Figure::factory()->create(['category_id' => $category->id, 'is_active' => false, 'stock' => 3]);

        $this->getJson("/api/public/products/{$active->id}")->assertOk()->assertJsonMissingPath('data.cost');
        $this->getJson("/api/public/products/{$inactive->id}")->assertNotFound();
    }

    public function test_non_admin_cannot_use_management_api(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user, 'sanctum')->getJson('/api/products')->assertForbidden();
    }

    public function test_admin_can_reach_top_selling(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin, 'sanctum')->getJson('/api/products/top-selling')->assertOk();
    }

    public function test_figure_detail_and_filters_render(): void
    {
        $category = Category::factory()->create(['slug' => 'marvel']);
        $figure = Figure::factory()->featured()->create(['category_id' => $category->id, 'name' => 'Iron Maceta']);
        Figure::factory()->outOfStock()->create(['category_id' => $category->id, 'name' => 'Agotada Uno']);

        $this->get("/catalog/{$figure->slug}")->assertOk()->assertSee('Iron Maceta');
        $this->get('/?category=marvel&search=Iron')->assertOk()->assertSee('Iron Maceta');
    }

    public function test_admin_panel_requires_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/figures')->assertOk();
        $this->actingAs($admin)->get('/admin/categories')->assertOk();
    }

    public function test_version_endpoint_and_footer(): void
    {
        $version = trim(file_get_contents(base_path('VERSION')));

        $this->getJson('/version.json')->assertOk()->assertJsonPath('version', $version);
        $this->get('/')->assertSee('v'.$version);
    }

    public function test_uploaded_images_are_converted_to_webp_and_compressed(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $big = UploadedFile::fake()->image('foto.jpg', 3000, 2000);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/upload/image', ['image' => $big])->assertOk();

        $path = $response->json('path');
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $info = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame('image/webp', $info['mime']);
        $this->assertSame(ImageOptimizer::MAX_SIDE, max($info[0], $info[1]));
    }

    public function test_catalog_seeder_loads_images_and_is_idempotent(): void
    {
        Storage::fake('public');

        $this->seed(CatalogSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->assertSame(585, Figure::count());
        $this->assertSame(0, Figure::whereNull('cost')->count());
        $this->assertSame(0, Figure::where('price', '<=', 0)->count());
        $this->assertSame(585, Figure::active()->count());
        $this->assertEquals(85, Figure::where('name', 'Aladinn')->value('cost')); // sin precio en el Excel
        $this->assertEquals(160, Figure::where('name', 'Aladinn')->value('price'));
        $this->assertEquals(95, Figure::where('name', 'Tiranosaurus')->value('cost')); // con precio en el Excel
        $this->assertEquals(170, Figure::where('name', 'Tiranosaurus')->value('price')); // costo + $75
        $withImage = Figure::whereNotNull('image')->count();
        $this->assertGreaterThan(350, $withImage);
        $this->assertStringEndsWith('.webp', Figure::whereNotNull('image')->first()->image);
    }

    public function test_figures_api_filters_and_stats(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();
        Figure::factory()->create(['category_id' => $category->id, 'name' => 'Goku', 'sku' => 'FM-1', 'price' => 0, 'is_active' => false, 'stock' => 0]);
        Figure::factory()->create(['category_id' => $category->id, 'name' => 'Vegeta', 'sku' => 'FM-2', 'price' => 200, 'stock' => 2, 'min_stock' => 5]);

        $this->actingAs($admin, 'sanctum');

        $this->getJson('/api/figures?search=goku')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/figures?no_price=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Goku');
        $this->getJson('/api/figures?active=0')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/figures?search=100%25')->assertOk()->assertJsonCount(0, 'data');

        $this->getJson('/api/figures/stats')->assertOk()
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.inactive', 1)
            ->assertJsonPath('data.no_price', 1)
            ->assertJsonPath('data.low_stock', 1)
            ->assertJsonPath('data.inventory_value', 400);
    }

    public function test_catalog_seeder_prices_figures_loaded_before_without_touching_captured_ones(): void
    {
        Storage::fake('public');
        $this->seed(CatalogSeeder::class);

        // Simula el estado anterior: una sin precio, otra con precio capturado a mano.
        $sinPrecio = Figure::where('sku', 'FM-0001')->first();
        $sinPrecio->update(['cost' => null, 'price' => 0, 'is_active' => false]);
        $capturada = Figure::where('sku', 'FM-0002')->first();
        $capturada->update(['cost' => 100, 'price' => 300, 'is_active' => false]);

        $this->seed(CatalogSeeder::class);

        $this->assertEquals(85, $sinPrecio->fresh()->cost);
        $this->assertEquals(160, $sinPrecio->fresh()->price);
        $this->assertTrue($sinPrecio->fresh()->is_active);
        $this->assertEquals(300, $capturada->fresh()->price); // no se pisa lo capturado
        $this->assertFalse($capturada->fresh()->is_active);
    }

    public function test_catalog_seeder_fixes_prices_it_calculated_with_the_old_formula(): void
    {
        Storage::fake('public');
        $this->seed(CatalogSeeder::class);

        // Estado anterior: costo × 2 redondeado a $5.
        $a = Figure::where('sku', 'FM-0001')->first();
        $a->update(['cost' => 85, 'price' => 170]);
        $b = Figure::where('name', 'Tiranosaurus')->first();
        $b->update(['cost' => 95, 'price' => 190]);
        $c = Figure::where('sku', 'FM-0002')->first();
        $c->update(['cost' => 85, 'price' => 200]); // precio capturado a mano

        $this->seed(CatalogSeeder::class);

        $this->assertEquals(160, $a->fresh()->price);
        $this->assertEquals(170, $b->fresh()->price);
        $this->assertEquals(200, $c->fresh()->price);
        $this->assertEquals(85, $a->fresh()->cost); // el costo nunca cambia
    }
}
