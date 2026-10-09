<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Figure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
