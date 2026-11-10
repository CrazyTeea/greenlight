<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_lists_demo_accounts(): void
    {
        User::factory()->count(3)->create();

        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->has('accounts', 3));
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_can_log_in_without_password(): void
    {
        $user = User::factory()->manager()->create();

        $this->post('/login', ['user_id' => $user->id])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
