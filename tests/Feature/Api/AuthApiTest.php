<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_a_customer_profile_and_returns_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Prachi',
            'email' => 'prachi@example.com',
            'password' => 'Shop@1234',
            'password_confirmation' => 'Shop@1234',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('messages.auth.registered'))
            ->assertJsonPath('data.user.email', 'prachi@example.com')
            ->assertJsonPath('data.user.role', User::ROLE_CUSTOMER);

        $this->assertDatabaseHas('users', [
            'email' => 'prachi@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $user = User::where('email', 'prachi@example.com')->firstOrFail();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
        ]);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_rejects_invalid_credentials_with_api_error_shape(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonPath('errors.email.0', __('messages.auth.credentials_invalid'));
    }
}
