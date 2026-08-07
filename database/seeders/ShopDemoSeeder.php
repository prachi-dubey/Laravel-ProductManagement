<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Tag;
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
            ]
        );

        $address = Address::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'label' => 'Home',
            ],
            [
                'line1' => '12 MG Road',
                'line2' => 'Near Metro',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560001',
                'country' => 'IN',
                'is_default' => true,
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

        $tags = collect(['Bestseller', 'New Arrival', 'Sale', 'Premium'])
            ->map(function (string $name) {
                return Tag::query()->updateOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                );
            });

        $catalog = [
            ['Electronics', 'Wireless Headphones', 'WH-100', 2499.00, 40],
            ['Electronics', 'USB-C Charger 65W', 'CHG-65', 1299.00, 80],
            ['Home & Kitchen', 'Ceramic Mug Set', 'MUG-4', 699.00, 55],
            ['Home & Kitchen', 'Steel Water Bottle', 'BTL-1L', 499.00, 120],
            ['Books', 'Laravel From Scratch', 'BK-LFS', 899.00, 30],
            ['Books', 'Clean Architecture Notes', 'BK-CAN', 799.00, 25],
        ];

        $products = collect($catalog)->map(function (array $row) use ($categories) {
            [$categoryName, $name, $sku, $price, $stock] = $row;
            $category = $categories->firstWhere('name', $categoryName);

            return Product::query()->updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => "Demo product: {$name}",
                    'price' => $price,
                    'stock' => $stock,
                    'is_active' => true,
                ]
            );
        });

        foreach ($products as $index => $product) {
            $product->tags()->sync(
                $tags->random(fake()->numberBetween(1, 3))->pluck('id')->all()
            );
        }

        $order = Order::query()->updateOrCreate(
            ['number' => 'ORD-DEMO-0001'],
            [
                'user_id' => $customer->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING,
                'subtotal' => 0,
                'total' => 0,
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
