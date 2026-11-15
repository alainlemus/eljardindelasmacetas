<?php

namespace Tests\Feature;

use App\Filament\Resources\SocialLinkResource\Pages\ManageSocialLinks;
use App\Filament\Resources\UserResource\Pages\ManageUsers;
use App\Models\Figure;
use App\Models\SocialLink;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_settings_pages_are_admin_only(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $urls = ['/admin/social-links', '/admin/users'];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
        foreach ($urls as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
        foreach ($urls as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    /** Como una petición nueva de la app: sin el usuario que quedó del panel en la prueba. */
    private function appRequest(string $token)
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }

    // ───────── Redes sociales ─────────

    public function test_can_add_whatsapp_and_it_receives_the_orders(): void
    {
        Livewire::actingAs($this->admin)->test(ManageSocialLinks::class)
            ->callAction('create', ['platform' => 'whatsapp', 'phone' => '+52 55 1234-5678', 'is_active' => true])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('social_links', ['platform' => 'whatsapp', 'phone' => '525512345678', 'url' => null]);
        $this->assertStringStartsWith('https://wa.me/525512345678?text=', Figure::whatsappUrl('hola', true));
    }

    public function test_deleting_or_hiding_whatsapp_stops_using_the_number(): void
    {
        $link = SocialLink::create(['platform' => 'whatsapp', 'phone' => '525512345678']);
        $this->assertSame('525512345678', SocialLink::whatsappNumber());

        $lw = Livewire::actingAs($this->admin)->test(ManageSocialLinks::class);

        $link->update(['is_active' => false]);
        $this->assertNull(SocialLink::whatsappNumber());

        $lw->callAction(TestAction::make(DeleteAction::class)->table($link));
        $this->assertModelMissing($link);
        $this->assertStringStartsWith('https://wa.me/?text=', Figure::whatsappUrl('hola', true));
    }

    public function test_social_network_requires_a_valid_url(): void
    {
        Livewire::actingAs($this->admin)->test(ManageSocialLinks::class)
            ->callAction('create', ['platform' => 'instagram', 'url' => 'no-es-un-enlace'])
            ->assertHasActionErrors(['url' => 'url']);

        Livewire::actingAs($this->admin)->test(ManageSocialLinks::class)
            ->callAction('create', ['platform' => 'whatsapp', 'phone' => ''])
            ->assertHasActionErrors(['phone' => 'required']);
    }

    public function test_links_survive_a_real_cache_store(): void
    {
        // El store "array" de las pruebas no serializa; database/redis sí y no admiten objetos.
        config(['cache.default' => 'file']);
        SocialLink::flushCache();
        SocialLink::create(['platform' => 'whatsapp', 'phone' => '525512345678']);

        SocialLink::active();
        $this->assertSame('525512345678', SocialLink::whatsappNumber());
        $this->get('/')->assertOk()->assertSee('href="https://wa.me/525512345678"', false);

        SocialLink::flushCache();
    }

    public function test_can_edit_a_social_link(): void
    {
        $link = SocialLink::create(['platform' => 'instagram', 'url' => 'https://instagram.com/viejo']);

        Livewire::actingAs($this->admin)->test(ManageSocialLinks::class)
            ->callAction(TestAction::make(EditAction::class)->table($link), ['url' => 'https://instagram.com/nuevo'])
            ->assertHasNoFormErrors();

        $this->assertSame('https://instagram.com/nuevo', $link->fresh()->url);
    }

    public function test_catalog_footer_and_structured_data_show_active_networks(): void
    {
        SocialLink::create(['platform' => 'instagram', 'url' => 'https://instagram.com/eljardin']);
        SocialLink::create(['platform' => 'facebook', 'url' => 'https://facebook.com/oculta', 'is_active' => false]);
        SocialLink::create(['platform' => 'whatsapp', 'phone' => '525512345678']);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://instagram.com/eljardin"', false)
            ->assertSee('href="https://wa.me/525512345678"', false)
            ->assertDontSee('facebook.com/oculta')
            ->assertSee('"sameAs":["https://instagram.com/eljardin"]', false);
    }

    // ───────── Usuarios ─────────

    public function test_can_create_an_admin_who_can_use_the_panel_and_the_app(): void
    {
        Livewire::actingAs($this->admin)->test(ManageUsers::class)
            ->callAction('create', [
                'name' => 'Ana',
                'email' => 'ana@example.com',
                'password' => 'secreto123',
                'is_admin' => true,
            ])
            ->assertHasNoFormErrors();

        $ana = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertTrue($ana->is_admin);
        $this->assertTrue(Hash::check('secreto123', $ana->password));

        $token = $this->postJson('/api/auth/login', ['email' => 'ana@example.com', 'password' => 'secreto123'])
            ->assertOk()
            ->json('token');
        $this->appRequest($token)->getJson('/api/figures')->assertOk();
    }

    public function test_non_admin_user_can_log_into_the_app_but_not_manage_it(): void
    {
        Livewire::actingAs($this->admin)->test(ManageUsers::class)
            ->callAction('create', ['name' => 'Beto', 'email' => 'beto@example.com', 'password' => 'secreto123', 'is_admin' => false])
            ->assertHasNoFormErrors();

        $token = $this->postJson('/api/auth/login', ['email' => 'beto@example.com', 'password' => 'secreto123'])
            ->assertOk()
            ->json('token');
        $this->appRequest($token)->getJson('/api/figures')->assertForbidden();
    }

    public function test_create_user_validates(): void
    {
        Livewire::actingAs($this->admin)->test(ManageUsers::class)
            ->callAction('create', ['name' => '', 'email' => $this->admin->email, 'password' => 'corta'])
            ->assertHasActionErrors(['name' => 'required', 'email' => 'unique', 'password' => 'min']);
    }

    public function test_editing_without_password_keeps_it_and_changing_it_logs_out_the_app(): void
    {
        $user = User::factory()->create(['password' => 'original123']);
        $user->createToken('api-token');

        $lw = Livewire::actingAs($this->admin)->test(ManageUsers::class);

        $lw->callAction(TestAction::make(EditAction::class)->table($user), ['name' => 'Nuevo nombre', 'password' => ''])
            ->assertHasNoFormErrors();
        $this->assertTrue(Hash::check('original123', $user->fresh()->password));
        $this->assertSame(1, $user->tokens()->count());

        $lw->callAction(TestAction::make(EditAction::class)->table($user), ['password' => 'cambiada123'])
            ->assertHasNoFormErrors();
        $this->assertTrue(Hash::check('cambiada123', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_admin_cannot_delete_or_demote_themselves(): void
    {
        Livewire::actingAs($this->admin)->test(ManageUsers::class)
            ->assertActionHidden(TestAction::make(DeleteAction::class)->table($this->admin))
            ->callAction(TestAction::make(EditAction::class)->table($this->admin), ['is_admin' => false])
            ->assertHasNoFormErrors();

        $this->assertTrue($this->admin->fresh()->is_admin);
    }

    public function test_can_delete_another_user_and_its_app_sessions(): void
    {
        $user = User::factory()->create();
        $user->createToken('api-token');

        Livewire::actingAs($this->admin)->test(ManageUsers::class)
            ->callAction(TestAction::make(DeleteAction::class)->table($user));

        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }
}
