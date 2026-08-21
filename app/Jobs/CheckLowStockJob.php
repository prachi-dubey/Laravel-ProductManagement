<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * After an order, warn when any line item product stock is at/below threshold.
 */
class CheckLowStockJob implements ShouldQueue
{
    use Queueable;

    public const THRESHOLD = 10;

    /** @var Order */
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        $order = $this->order->loadMissing(['items.product']);

        $admins = User::query()->where('role', User::ROLE_ADMIN)->get();

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            if ($product->stock > self::THRESHOLD) {
                continue;
            }

            Log::warning('Low stock detected', [
                'product_id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'threshold' => self::THRESHOLD,
                'order_id' => $order->id,
            ]);

            foreach ($admins as $admin) {
                $admin->notify(new LowStockNotification($product));
            }
        }
    }
}
