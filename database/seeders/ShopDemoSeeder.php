<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopDemoSeeder extends Seeder
{
    /**
     * Seed realistic demo data for the shop domain (Postman practice later).
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@shop.test'],
            [
                'name' => 'Shop Admin',
                'role' => User::ROLE_ADMIN,
                'password' => 'password',
            ]
        );

        Profile::query()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'phone' => '+919876543210',
                'bio' => 'Store administrator',
            ]
        );

        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@shop.test'],
            [
                'name' => 'Demo Customer',
                'role' => User::ROLE_CUSTOMER,
                'password' => 'password',
            ]
        );

        Profile::query()->updateOrCreate(
            ['user_id' => $customer->id],
            [
                'phone' => '+919811122233',
                'bio' => 'Regular customer account',
                'line1' => '12 MG Road',
                'line2' => 'Near Metro',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560001',
                'country' => 'IN',
            ]
        );

        $categories = collect([
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen'],
            ['name' => 'Books', 'slug' => 'books'],
        ])->map(function (array $data) {
            return Category::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['name'].' products for the demo shop.',
                    'is_active' => true,
                ]
            );
        });

        $electronics = $categories->firstWhere('name', 'Electronics');
        $homeKitchen = $categories->firstWhere('name', 'Home & Kitchen');
        $books = $categories->firstWhere('name', 'Books');

        $catalog = [
            ['Wireless Headphones', 'WH-100', 2499.00, 40, [$electronics->id]],
            ['USB-C Charger 65W', 'CHG-65', 1299.00, 80, [$electronics->id]],
            ['Ceramic Mug Set', 'MUG-4', 699.00, 55, [$homeKitchen->id]],
            ['Steel Water Bottle', 'BTL-1L', 499.00, 120, [$homeKitchen->id, $electronics->id]],
            ['Laravel From Scratch', 'BK-LFS', 899.00, 30, [$books->id]],
            ['Clean Architecture Notes', 'BK-CAN', 799.00, 25, [$books->id, $electronics->id]],
        ];

        $products = collect($catalog)->map(function (array $row) {
            [$name, $sku, $price, $stock, $categoryIds] = $row;

            $product = Product::query()->updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => "Demo product: {$name}",
                    'price' => $price,
                    'stock' => $stock,
                    'is_active' => true,
                ]
            );

            $product->categories()->sync($categoryIds);

            return $product;
        });

        $order = Order::query()->updateOrCreate(
            ['number' => 'ORD-DEMO-0001'],
            [
                'user_id' => $customer->id,
                'status' => Order::STATUS_PENDING,
                'subtotal' => 0,
                'total' => 0,
                'shipping_line1' => '12 MG Road',
                'shipping_line2' => 'Near Metro',
                'shipping_city' => 'Bengaluru',
                'shipping_state' => 'Karnataka',
                'shipping_postal_code' => '560001',
                'shipping_country' => 'IN',
                'placed_at' => now(),
            ]
        );

        $order->items()->delete();

        $lineProducts = $products->take(2);
        $subtotal = 0;

        foreach ($lineProducts as $product) {
            $quantity = 1;
            $lineTotal = bcmul((string) $product->price, (string) $quantity, 2);
            $subtotal = bcadd((string) $subtotal, $lineTotal, 2);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'line_total' => $lineTotal,
            ]);
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    }
}
