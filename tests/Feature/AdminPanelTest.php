<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource\Pages\ManageCategories;
use App\Filament\Resources\FigureResource\Pages\ManageFigures;
use App\Models\Category;
use App\Models\Figure;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['/admin', '/admin/figures', '/admin/categories'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
    }

    public function test_non_admins_are_blocked_everywhere(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        foreach (['/admin', '/admin/figures', '/admin/categories'] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_all_admin_pages_load(): void
    {
        Figure::factory()->count(3)->create();

        foreach (['/admin', '/admin/figures', '/admin/categories'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_figures_table_lists_and_searches(): void
    {
        $goku = Figure::factory()->create(['name' => 'Goku Maceta']);
        $vegeta = Figure::factory()->create(['name' => 'Vegeta Maceta']);

        Livewire::actingAs($this->admin)->test(ManageFigures::class)
            ->assertCanSeeTableRecords([$goku, $vegeta])
            ->searchTable('Goku')
            ->assertCanSeeTableRecords([$goku])
            ->assertCanNotSeeTableRecords([$vegeta]);
    }

    public function test_figures_table_filters(): void
    {
        $inactive = Figure::factory()->create(['is_active' => false, 'stock' => 3]);
        $active = Figure::factory()->create(['is_active' => true, 'stock' => 0]);

        Livewire::actingAs($this->admin)->test(ManageFigures::class)
            ->filterTable('active')
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive])
            ->resetTableFilters()
            ->filterTable('in_stock')
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_can_create_a_figure(): void
    {
        $category = Category::factory()->create();

        Livewire::actingAs($this->admin)->test(ManageFigures::class)
            ->callAction('create', [
                'name' => 'Spiderman',
                'slug' => 'spiderman',
                'sku' => 'FM-9001',
                'category_id' => $category->id,
                'price' => 160,
                'cost' => 85,
                'stock' => 4,
                'min_stock' => 2,
                'is_active' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('figures', ['sku' => 'FM-9001', 'slug' => 'spiderman', 'stock' => 4]);
    }

    public function test_create_figure_validates_required_and_unique(): void
    {
        Figure::factory()->create(['sku' => 'FM-1', 'slug' => 'dup']);

        Livewire::actingAs($this->admin)->test(ManageFigures::class)
            ->callAction('create', ['name' => '', 'sku' => 'FM-1', 'slug' => 'dup', 'price' => null])
            ->assertHasActionErrors(['name' => 'required', 'price' => 'required', 'sku' => 'unique', 'slug' => 'unique']);
    }

    public function test_typing_a_name_fills_the_slug(): void
    {
        Livewire::actingAs($this->admin)->test(ManageFigures::class)
            ->mountAction('create')
            ->fillForm(['name' => 'Capitán América'])
            ->assertSet('mountedActions.0.data.slug', 'capitan-america');
    }

    public function test_can_edit_a_figure(): void
    {
        $figure = Figure::factory()->create(['price' => 100]);

        Livewire::actingAs($this->admin)->test(ManageFigures::class)
            ->callAction(TestAction::make(EditAction::class)->table($figure), ['price' => 175, 'stock' => 9])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('figures', ['id' => $figure->id, 'price' => 175, 'stock' => 9]);
    }

    public function test_can_delete_a_figure_and_bulk_toggle(): void
    {
        $a = Figure::factory()->create(['is_active' => true]);
        $b = Figure::factory()->create(['is_active' => true]);
        $c = Figure::factory()->create();

        Livewire::actingAs($this->admin)->test(ManageFigures::class)
            ->callAction(TestAction::make(DeleteAction::class)->table($c))
            ->selectTableRecords([$a->id, $b->id])
            ->callAction(TestAction::make('deactivate')->table()->bulk());

        $this->assertModelMissing($c);
        $this->assertFalse($a->fresh()->is_active);
        $this->assertFalse($b->fresh()->is_active);
    }

    public function test_category_crud(): void
    {
        $lw = Livewire::actingAs($this->admin)->test(ManageCategories::class);

        $lw->callAction('create', ['name' => 'Anime', 'slug' => 'anime', 'is_active' => true])
            ->assertHasNoFormErrors();
        $category = Category::where('slug', 'anime')->firstOrFail();

        $lw->assertCanSeeTableRecords([$category])
            ->callAction(TestAction::make(EditAction::class)->table($category), ['name' => 'Anime 2'])
            ->assertHasNoFormErrors();
        $this->assertSame('Anime 2', $category->fresh()->name);

        $lw->callAction(TestAction::make(DeleteAction::class)->table($category));
        $this->assertModelMissing($category);
    }

    public function test_deleting_a_category_keeps_its_figures(): void
    {
        $category = Category::factory()->create();
        $figure = Figure::factory()->create(['category_id' => $category->id]);

        Livewire::actingAs($this->admin)->test(ManageCategories::class)
            ->callAction(TestAction::make(DeleteAction::class)->table($category));

        $this->assertModelExists($figure);
        $this->assertNull($figure->fresh()->category_id);
    }

    public function test_logout_works(): void
    {
        $this->actingAs($this->admin)->post('/admin/logout')->assertRedirect();
        $this->assertGuest();
    }
}
