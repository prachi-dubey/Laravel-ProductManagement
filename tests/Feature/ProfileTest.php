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

    public function test_update_profile_sets_shipping_address(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', [
            'phone' => '+919876543210',
            'line1' => '123 Main Street',
            'city' => 'Indore',
            'state' => 'MP',
            'postal_code' => '452001',
            'country' => 'IN',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('messages.auth.profile_updated'))
            ->assertJsonPath('data.profile.line1', '123 Main Street')
            ->assertJsonPath('data.profile.city', 'Indore')
            ->assertJsonPath('data.profile.country', 'IN');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'line1' => '123 Main Street',
            'city' => 'Indore',
            'postal_code' => '452001',
            'country' => 'IN',
        ]);
    }

    public function test_update_profile_then_place_order_succeeds(): void
    {
        $user = User::factory()->customer()->create();
        $user->profile()->create([]);

        Sanctum::actingAs($user);

        $this->putJson('/api/profile', [
            'line1' => '123 Main Street',
            'city' => 'Indore',
            'postal_code' => '452001',
            'country' => 'IN',
        ])->assertOk();

        $product = \App\Models\Product::factory()->create([
            'price' => 100,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');
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
