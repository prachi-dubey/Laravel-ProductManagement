<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationsAndDeleteConstraintsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_category_if_category_has_products(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create(['name' => 'Electronics']);
        $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);
        $product->categories()->sync([$category->id]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/categories/'.$category->id);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'CATEGORY_IN_USE');
    }

    public function test_admin_cannot_delete_product_if_product_has_order_items(): void
    {
        $admin = User::factory()->admin()->create();

        $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => $product->price,
            'line_total' => $product->price,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/products/'.$product->id);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'PRODUCT_IN_USE');
    }
}

