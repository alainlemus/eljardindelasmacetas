<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\FigureResource\Pages\ManageFigures;
use App\Models\Figure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_the_two_section_layout(): void
    {
        $this->get('/admin/login')->assertOk()
            ->assertSee('jm-brand', false)      // sección izquierda: marca
            ->assertSee('jm-form', false)       // sección derecha: formulario
            ->assertSee('Bienvenido de nuevo')
            ->assertSee('El Jardín de las Macetas');
    }

    public function test_admin_can_sign_in_from_the_new_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'secreto123']);

        Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'secreto123'])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'secreto123']);

        Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'incorrecta'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_non_admin_cannot_enter_the_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'password' => 'secreto123']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_dashboard_stats_render_without_lazy_loading(): void
    {
        Figure::factory()->count(3)->create(['price' => 0]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('Total de figuras')
            ->assertSee('Sin precio')
            ->assertSee('Valor del inventario');
    }

    public function test_panel_shows_prices_in_pesos(): void
    {
        Figure::factory()->create(['name' => 'Goku', 'price' => 160]);
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)->test(ManageFigures::class)
            ->assertSee('$160.00')
            ->assertDontSee('US$');
    }
}
