<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Figure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Flujos que ejecuta la app móvil contra la API (login, categorías, figuras, fotos, tablero).
 */
class AppFlowsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->create(['is_admin' => true, 'password' => 'secreto123']);
    }

    private function asAdmin(): static
    {
        return $this->actingAs($this->admin, 'sanctum');
    }

    // ---------- Autenticación ----------

    public function test_login_returns_token_and_user_then_me_and_logout(): void
    {
        $response = $this->postJson('/api/auth/login', ['email' => $this->admin->email, 'password' => 'secreto123'])
            ->assertOk()
            ->assertJsonPath('user.email', $this->admin->email)
            ->assertJsonMissingPath('user.password');

        $token = $response->json('token');
        $this->assertNotEmpty($token);

        $this->withToken($token)->getJson('/api/auth/me')->assertOk()->assertJsonPath('user.is_admin', true);
        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        $this->assertSame(0, $this->admin->tokens()->count());
    }

    public function test_login_validation_and_wrong_credentials(): void
    {
        $this->postJson('/api/auth/login', [])->assertStatus(422)->assertJsonValidationErrors(['email', 'password']);
        $this->postJson('/api/auth/login', ['email' => $this->admin->email, 'password' => 'mala'])
            ->assertStatus(422)->assertJsonValidationErrors('email');
        $this->postJson('/api/auth/login', ['email' => 'nadie@x.com', 'password' => 'x'])->assertStatus(422);
    }

    public function test_requests_without_token_get_401_json_and_non_admins_get_403(): void
    {
        $this->getJson('/api/figures')->assertUnauthorized();
        $this->getJson('/api/categories')->assertUnauthorized();

        $user = User::factory()->create(['is_admin' => false]);
        foreach (['/api/figures', '/api/categories', '/api/figures/stats'] as $url) {
            $this->actingAs($user, 'sanctum')->getJson($url)->assertForbidden();
        }
        $this->actingAs($user, 'sanctum')->postJson('/api/upload/image')->assertForbidden();
    }

    // ---------- Categorías ----------

    public function test_category_crud_toggle_and_delete_rules(): void
    {
        $this->asAdmin();

        $id = $this->postJson('/api/categories', ['name' => 'Marvel', 'description' => 'Héroes'])
            ->assertCreated()->assertJsonPath('data.slug', 'marvel')->json('data.id');

        $this->postJson('/api/categories', ['name' => 'Marvel'])->assertStatus(422)->assertJsonValidationErrors('name');
        $this->postJson('/api/categories', [])->assertStatus(422);

        $this->putJson("/api/categories/{$id}", ['name' => 'Marvel Studios'])->assertOk()->assertJsonPath('data.slug', 'marvel-studios');
        $this->putJson("/api/categories/{$id}", ['name' => 'Marvel Studios'])->assertOk(); // mismo nombre: no choca consigo misma

        $other = Category::factory()->create(['name' => 'DC']);
        $this->putJson("/api/categories/{$id}", ['name' => 'DC'])->assertStatus(422);

        $this->patchJson("/api/categories/{$id}/toggle-active")->assertOk()->assertJsonPath('data.is_active', false);

        Figure::factory()->create(['category_id' => $id]);
        $this->getJson('/api/categories')->assertOk()->assertJsonFragment(['figures_count' => 1]);
        $this->deleteJson("/api/categories/{$id}")->assertStatus(422);

        $this->deleteJson("/api/categories/{$other->id}")->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $other->id]);
    }

    // ---------- Figuras ----------

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'Goku',
            'sku' => 'FM-0001',
            'price' => 250,
            'stock' => 3,
            'cost' => 120,
            'min_stock' => 5,
            'is_active' => true,
            'is_featured' => false,
        ], $over);
    }

    public function test_create_figure_with_the_payload_the_app_sends(): void
    {
        $category = Category::factory()->create();

        $response = $this->asAdmin()->postJson('/api/figures', $this->payload([
            'category_id' => $category->id,
            'description' => 'Maceta de Goku',
            'image' => 'http://sistema-funkos.test/storage/funkomacetas/a.webp',
            'images' => ['http://sistema-funkos.test/storage/funkomacetas/a.webp', 'http://otro.host/storage/funkomacetas/b.webp'],
        ]))->assertCreated();

        $figure = Figure::first();
        $this->assertSame('goku', $figure->slug);
        $this->assertSame('funkomacetas/a.webp', $figure->image);
        $this->assertSame(['funkomacetas/a.webp', 'funkomacetas/b.webp'], $figure->images);
        $response->assertJsonPath('data.category.id', $category->id);
    }

    public function test_create_inactive_figure_without_price_like_the_supplier_catalog(): void
    {
        $this->asAdmin()->postJson('/api/figures', $this->payload(['price' => 0, 'is_active' => false]))->assertCreated();
        $this->assertDatabaseHas('figures', ['sku' => 'FM-0001', 'is_active' => false, 'price' => 0]);
    }

    public function test_create_validation_and_duplicate_sku(): void
    {
        $this->asAdmin();
        $this->postJson('/api/figures', [])->assertStatus(422)->assertJsonValidationErrors(['name', 'sku', 'price', 'stock']);
        $this->postJson('/api/figures', $this->payload(['price' => -1, 'stock' => -2]))->assertStatus(422);
        $this->postJson('/api/figures', $this->payload(['category_id' => 999]))->assertStatus(422)->assertJsonValidationErrors('category_id');

        $this->postJson('/api/figures', $this->payload())->assertCreated();
        $this->postJson('/api/figures', $this->payload(['name' => 'Otra']))->assertStatus(422)->assertJsonValidationErrors('sku');
    }

    public function test_same_name_in_different_categories_gets_unique_slugs_and_can_be_edited(): void
    {
        // Reproduce el error de la app: "Cenicienta" existe en Personajes y en Posket.
        $personajes = Category::factory()->create(['name' => 'Personajes']);
        $posket = Category::factory()->create(['name' => 'Personajes Posket']);

        $a = Figure::factory()->create(['name' => 'Cenicienta', 'slug' => 'cenicienta', 'category_id' => $personajes->id]);
        $b = Figure::factory()->create(['name' => 'Cenicienta', 'slug' => 'cenicienta-personajes-posket', 'category_id' => $posket->id]);

        $this->asAdmin();
        // La app reenvía el nombre completo aunque no cambie.
        $this->putJson("/api/figures/{$b->id}", $this->payload(['name' => 'Cenicienta', 'sku' => $b->sku, 'category_id' => $posket->id, 'price' => 270]))
            ->assertOk()->assertJsonPath('data.slug', 'cenicienta-personajes-posket');

        // Y crear otra con el mismo nombre tampoco rompe.
        $this->postJson('/api/figures', $this->payload(['name' => 'Cenicienta', 'sku' => 'NEW-1', 'category_id' => $posket->id]))->assertCreated();
        $this->assertSame(3, Figure::where('name', 'Cenicienta')->count());
        $this->assertSame(3, Figure::pluck('slug')->unique()->count());

        // Renombrar a un nombre que ya existe genera slug distinto.
        $c = Figure::factory()->create(['name' => 'Otra', 'category_id' => $personajes->id]);
        $this->putJson("/api/figures/{$c->id}", ['name' => 'Cenicienta'])->assertOk();
        $this->assertSame(4, Figure::pluck('slug')->unique()->count());
    }

    public function test_update_partial_keeps_slug_and_allows_clearing_photos(): void
    {
        $figure = Figure::factory()->create(['name' => 'Goku', 'slug' => 'goku', 'image' => 'funkomacetas/a.webp', 'images' => ['funkomacetas/a.webp']]);

        $this->asAdmin()->putJson("/api/figures/{$figure->id}", ['price' => 300])->assertOk()->assertJsonPath('data.slug', 'goku');
        $this->putJson("/api/figures/{$figure->id}", ['image' => null, 'images' => []])->assertOk();

        $figure->refresh();
        $this->assertNull($figure->image);
        $this->assertSame([], $figure->images);
        $this->assertEquals(300, $figure->price);
    }

    public function test_update_sku_rules(): void
    {
        $a = Figure::factory()->create(['sku' => 'A-1']);
        $b = Figure::factory()->create(['sku' => 'B-1']);

        $this->asAdmin()->putJson("/api/figures/{$a->id}", ['sku' => 'A-1'])->assertOk();
        $this->putJson("/api/figures/{$a->id}", ['sku' => 'B-1'])->assertStatus(422)->assertJsonValidationErrors('sku');
    }

    public function test_stock_toggle_sale_top_selling_show_and_delete(): void
    {
        $f = Figure::factory()->create(['stock' => 1, 'min_stock' => 5, 'sales_count' => 0]);
        $g = Figure::factory()->create(['sales_count' => 10]);

        $this->asAdmin();
        $this->patchJson("/api/figures/{$f->id}/stock", ['stock' => 15])->assertOk()->assertJsonPath('data.stock', 15);
        $this->patchJson("/api/figures/{$f->id}/stock", ['stock' => -1])->assertStatus(422);
        $this->patchJson("/api/figures/{$f->id}/stock", [])->assertStatus(422);

        $this->patchJson("/api/figures/{$f->id}/toggle-active")->assertOk()->assertJsonPath('data.is_active', false);
        $this->patchJson("/api/figures/{$f->id}/toggle-active")->assertOk()->assertJsonPath('data.is_active', true);

        $this->postJson("/api/figures/{$f->id}/sale", ['quantity' => 3])->assertOk()->assertJsonPath('data.sales_count', 3);
        $this->postJson("/api/figures/{$f->id}/sale")->assertOk()->assertJsonPath('data.sales_count', 4);
        $this->postJson("/api/figures/{$f->id}/sale", ['quantity' => 0])->assertStatus(422);

        $this->getJson('/api/figures/top-selling')->assertOk()->assertJsonPath('data.0.id', $g->id);
        $this->getJson('/api/figures/top-selling?limit=1')->assertOk()->assertJsonCount(1, 'data');

        $this->getJson("/api/figures/{$f->id}")->assertOk()->assertJsonPath('data.id', $f->id)->assertJsonPath('data.cost', $f->cost);
        $this->getJson('/api/figures/9999')->assertNotFound();

        $this->deleteJson("/api/figures/{$f->id}")->assertOk();
        $this->assertDatabaseMissing('figures', ['id' => $f->id]);
    }

    public function test_alias_routes_under_products_still_work(): void
    {
        $f = Figure::factory()->create();
        $this->asAdmin();
        $this->getJson('/api/products')->assertOk();
        $this->getJson("/api/products/{$f->id}")->assertOk();
        $this->patchJson("/api/products/{$f->id}/stock", ['stock' => 4])->assertOk();
        $this->getJson('/api/products/top-selling')->assertOk();
    }

    public function test_list_filters_search_pagination_and_sorting(): void
    {
        $cat = Category::factory()->create();
        Figure::factory()->create(['name' => 'Zoro', 'sku' => 'FM-9', 'category_id' => $cat->id, 'price' => 200]);
        Figure::factory()->create(['name' => 'Aang', 'sku' => 'FM-1', 'price' => 0, 'is_active' => false, 'stock' => 0]);
        Figure::factory()->create(['name' => 'Luffy', 'sku' => 'FM-5', 'price' => 150, 'stock' => 2, 'min_stock' => 5, 'is_featured' => true]);

        $this->asAdmin();
        $this->getJson('/api/figures')->assertOk()->assertJsonPath('data.0.name', 'Aang')->assertJsonPath('total', 3);
        $this->getJson('/api/figures?search=fm-5')->assertJsonCount(1, 'data');
        $this->getJson('/api/figures?search=zo')->assertJsonPath('data.0.name', 'Zoro');
        $this->getJson('/api/figures?search=')->assertJsonCount(3, 'data');
        $this->getJson('/api/figures?active=1')->assertJsonCount(2, 'data');
        $this->getJson('/api/figures?active=0')->assertJsonCount(1, 'data');
        $this->getJson('/api/figures?no_price=1')->assertJsonCount(1, 'data');
        $this->getJson('/api/figures?low_stock=1')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Luffy');
        $this->getJson('/api/figures?featured=1')->assertJsonCount(1, 'data');
        $this->getJson("/api/figures?category_id={$cat->id}")->assertJsonCount(1, 'data');
        $this->getJson('/api/figures?per_page=2&page=2')->assertJsonCount(1, 'data')->assertJsonPath('last_page', 2);
        $this->getJson('/api/figures?per_page=5000')->assertOk()->assertJsonPath('per_page', 100);
    }

    public function test_stats_for_dashboard(): void
    {
        Figure::factory()->create(['price' => 100, 'stock' => 2, 'min_stock' => 5]);               // stock bajo
        Figure::factory()->create(['price' => 0, 'stock' => 0, 'is_active' => false]);             // sin precio, inactiva
        Figure::factory()->create(['price' => 50, 'stock' => 10, 'min_stock' => 5]);

        $this->asAdmin()->getJson('/api/figures/stats')->assertOk()
            ->assertJsonPath('data.total', 3)
            ->assertJsonPath('data.active', 2)
            ->assertJsonPath('data.inactive', 1)
            ->assertJsonPath('data.no_price', 1)
            ->assertJsonPath('data.low_stock', 1)
            ->assertJsonPath('data.inventory_value', 700)
            ->assertJsonCount(1, 'data.low_stock_items');
    }

    // ---------- Fotos ----------

    public function test_single_and_multiple_uploads_are_webp_with_urls_the_app_can_save(): void
    {
        $this->asAdmin();

        $one = $this->postJson('/api/upload/image', ['image' => UploadedFile::fake()->image('a.jpg', 2000, 1500)])->assertOk();
        $this->assertStringEndsWith('.webp', $one->json('path'));
        $this->assertStringContainsString('/storage/funkomacetas/', $one->json('url'));

        $many = $this->postJson('/api/upload/images', ['images' => [
            UploadedFile::fake()->image('b.png', 800, 600),
            UploadedFile::fake()->image('c.jpg', 600, 900),
        ]])->assertOk();
        $this->assertCount(2, $many->json('urls'));

        // La app guarda la URL devuelta; el servidor la normaliza a ruta relativa.
        $this->postJson('/api/figures', $this->payload(['image' => $one->json('url'), 'images' => $many->json('urls')]))->assertCreated();
        $figure = Figure::first();
        $this->assertSame($one->json('path'), $figure->image);
        foreach ($figure->images as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_upload_rejects_invalid_files(): void
    {
        $this->asAdmin();
        $this->postJson('/api/upload/image', [])->assertStatus(422);
        $this->postJson('/api/upload/image', ['image' => UploadedFile::fake()->create('nota.txt', 5, 'text/plain')])->assertStatus(422);
        $this->postJson('/api/upload/image', ['image' => UploadedFile::fake()->create('grande.jpg', 20000, 'image/jpeg')])->assertStatus(422);
        $this->postJson('/api/upload/images', ['images' => []])->assertStatus(422);
    }

    public function test_corrupt_image_with_valid_extension_is_rejected_cleanly(): void
    {
        $file = UploadedFile::fake()->createWithContent('rota.jpg', 'esto no es una imagen');

        $this->asAdmin()->postJson('/api/upload/image', ['image' => $file])->assertStatus(422);
    }

    // ---------- Público ----------

    public function test_public_catalog_only_exposes_active_figures_and_hides_internal_data(): void
    {
        $cat = Category::factory()->create();
        $visible = Figure::factory()->create(['category_id' => $cat->id, 'cost' => 99, 'sales_count' => 7]);
        $hidden = Figure::factory()->inactive()->create(['category_id' => $cat->id]);

        $this->getJson('/api/public/catalog')->assertOk()->assertJsonPath('data.total_products', 1);
        $this->getJson('/api/public/figures')->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.cost');
        $this->getJson("/api/public/figures/{$visible->id}")->assertOk()->assertJsonMissingPath('data.cost')->assertJsonMissingPath('data.sales_count');
        $this->getJson("/api/public/figures/{$hidden->id}")->assertNotFound();
        $this->getJson('/api/public/products')->assertOk();

        // El administrador sí ve el costo en su listado.
        $this->asAdmin()->getJson("/api/figures/{$visible->id}")->assertJsonPath('data.cost', '99.00');
    }
}
