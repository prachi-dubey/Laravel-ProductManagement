<?php

namespace Tests\Feature\Api;

use App\Events\Order\OrderPlaced;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_category(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson('/api/categories', [
            'name' => 'Gaming',
            'description' => 'Gaming accessories',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Gaming')
            ->assertJsonPath('data.slug', 'gaming')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('categories', [
            'name' => 'Gaming',
            'slug' => 'gaming',
            'is_active' => 1,
        ]);
    }

    public function test_categories_index_applies_case_insensitive_sort_direction(): void
    {
        Category::factory()->create(['name' => 'Gamma']);
        Category::factory()->create(['name' => 'Alpha']);
        Category::factory()->create(['name' => 'Beta']);

        $response = $this->getJson('/api/categories?sort=name&sort_direction=AsC');

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $names = array_column($response->json('data'), 'name');

        $this->assertSame(['Alpha', 'Beta', 'Gamma'], $names);
    }

    public function test_products_index_defaults_sort_direction_to_asc_when_omitted(): void
    {
        Product::factory()->create(['price' => 500, 'name' => 'High']);
        Product::factory()->create(['price' => 100, 'name' => 'Low']);
        Product::factory()->create(['price' => 300, 'name' => 'Mid']);

        $response = $this->getJson('/api/products?sort=price');

        $response->assertOk();

        $prices = array_column($response->json('data'), 'price');

        $this->assertSame(['100.00', '300.00', '500.00'], $prices);
    }

    public function test_customer_can_place_an_order_and_stock_is_decremented(): void
    {
        Event::fake([OrderPlaced::class]);

        $customer = User::factory()->customer()->create();
        $customer->profile()->create([
            'line1' => 'Street 1',
            'city' => 'Indore',
            'postal_code' => '452001',
            'country' => 'IN',
        ]);

        Sanctum::actingAs($customer);

        $product = Product::factory()->create([
            'price' => 250,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('messages.orders.placed'))
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', '500.00')
            ->assertJsonPath('data.items.0.product_id', $product->id)
            ->assertJsonPath('data.items.0.quantity', 2);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => 'pending',
            'total' => 500,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'line_total' => 500,
        ]);
        $this->assertSame(3, $product->fresh()->stock);

        Event::assertDispatched(OrderPlaced::class);
    }
}
