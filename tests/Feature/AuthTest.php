<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_user_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'tech.test@helpdesk.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'tech.test@helpdesk.local',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'tech.test@helpdesk.local',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => 'tech.test@helpdesk.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_new_customer_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Customer',
            'email' => 'customer.john@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'customer.john@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
