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

    public function test_order_placement_creates_database_notifications_for_customer_and_admins(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = User::factory()->customer()->create();
        $customer->profile()->create([
            'line1' => 'Street 1',
            'city' => 'Indore',
            'postal_code' => '452001',
            'country' => 'IN',
        ]);

        // stock = 5, order quantity = 1 => stock after decrement = 4 (<= 10)
        $product = Product::factory()->create([
            'price' => 250,
            'stock' => 5,
            'is_active' => true,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $customer->refresh();
        $admin->refresh();

        $customerNotificationData = $customer->notifications()->get()->map(function ($n) {
            $data = $n->data;

            // In some setups $data may already be an array.
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            return $data;
        })->all();

        $this->assertTrue(
            collect($customerNotificationData)->contains(function (array $data): bool {
                return ($data['type'] ?? null) === 'order_placed';
            }),
            'Expected at least one order_placed database notification for customer.'
        );

        $adminNotificationData = $admin->notifications()->get()->map(function ($n) {
            $data = $n->data;
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            return $data;
        })->all();

        $this->assertTrue(
            collect($adminNotificationData)->contains(function (array $data) use ($product): bool {
                return ($data['type'] ?? null) === 'low_stock'
                    && ($data['product_id'] ?? null) === $product->id;
            }),
            'Expected at least one low_stock database notification for admin.'
        );
    }

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

