<?php

namespace Tests\Unit\Services;

use App\Events\Order\OrderPlaced;
use App\Exceptions\ApiException;
use App\Interfaces\Order\OrderRepositoryInterface;
use App\Interfaces\Product\ProductRepositoryInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function test_place_throws_when_shipping_address_is_missing(): void
    {
        $user = User::factory()->create();

        $orders = Mockery::mock(OrderRepositoryInterface::class);
        $products = Mockery::mock(ProductRepositoryInterface::class);

        $orders
            ->shouldReceive('findShippingProfile')
            ->once()
            ->with($user)
            ->andReturn(null);

        $service = new OrderService($orders, $products);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(__('messages.orders.invalid_address'));

        $service->place($user, [
            'items' => [
                ['product_id' => 1, 'quantity' => 1],
            ],
        ]);
    }

    public function test_place_creates_order_items_decrements_stock_and_dispatches_event(): void
    {
        Event::fake([OrderPlaced::class]);

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create([
            'line1' => 'Street 1',
            'city' => 'Indore',
            'postal_code' => '452001',
            'country' => 'IN',
        ]);

        $product = Product::factory()->create([
            'name' => 'Gaming Mouse',
            'price' => 250,
            'stock' => 5,
            'is_active' => true,
        ]);

        $order = Order::factory()->for($user)->create([
            'status' => Order::STATUS_PENDING,
            'subtotal' => 500,
            'total' => 500,
            'shipping_line1' => $profile->line1,
            'shipping_city' => $profile->city,
            'shipping_postal_code' => $profile->postal_code,
            'shipping_country' => $profile->country,
        ]);

        $orders = Mockery::mock(OrderRepositoryInterface::class);
        $products = Mockery::mock(ProductRepositoryInterface::class);

        $orders->shouldReceive('findShippingProfile')->once()->with($user)->andReturn($profile);
        $products->shouldReceive('findForUpdate')->once()->with($product->id)->andReturn($product);
        $orders->shouldReceive('numberExists')->once()->andReturn(false);
        $orders->shouldReceive('create')->once()->with(Mockery::on(function (array $payload) use ($user): bool {
            return $payload['user_id'] === $user->id
                && $payload['status'] === Order::STATUS_PENDING
                && (float) $payload['subtotal'] === 500.0
                && (float) $payload['total'] === 500.0
                && str_starts_with($payload['number'], 'ORD-');
        }))->andReturn($order);
        $orders->shouldReceive('addItem')->once()->with($order, Mockery::on(function (array $item) use ($product): bool {
            return $item['product_id'] === $product->id
                && $item['product_name'] === 'Gaming Mouse'
                && $item['quantity'] === 2
                && (float) $item['unit_price'] === 250.0
                && (float) $item['line_total'] === 500.0;
        }));
        $products->shouldReceive('decrementStock')->once()->with($product, 2);
        $orders->shouldReceive('loadRelations')->once()->with($order, ['items.product', 'user'])->andReturn($order);

        $service = new OrderService($orders, $products);

        $result = $service->place($user, [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $this->assertSame($order, $result);
        Event::assertDispatched(OrderPlaced::class);
    }
}
