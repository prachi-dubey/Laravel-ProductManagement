<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_returns_unauthenticated_when_token_is_missing(): void
    {
        $response = $this->getJson('/api/me');

        $response
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'UNAUTHENTICATED');
    }

    public function test_me_returns_user_and_profile_data(): void
    {
        $user = User::factory()->create();

        $user->profile()->create([
            'line1' => 'Street 1',
            'city' => 'Indore',
            'postal_code' => '452001',
            'country' => 'IN',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.profile.city', 'Indore');
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('messages.auth.logged_out'));
    }
}
