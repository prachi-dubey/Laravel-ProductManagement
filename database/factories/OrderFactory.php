<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);

        return [
            'number' => 'ORD-'.now()->format('Ymd').'-'.fake()->unique()->numerify('######'),
            'user_id' => User::factory(),
            'address_id' => null,
            'status' => Order::STATUS_PENDING,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'placed_at' => now(),
        ];
    }
}
