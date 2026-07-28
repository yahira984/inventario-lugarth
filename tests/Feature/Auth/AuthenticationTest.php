<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_warehouse_user_is_sent_to_inventory_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('materiales.index', absolute: false));
    }

    public function test_authenticated_warehouse_user_cannot_be_trapped_between_login_and_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect(route('materiales.index'));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('materiales.index'))
            ->assertSessionHas('warning');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
