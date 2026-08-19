<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Sends order confirmation email in the background.
 */
class SendOrderConfirmationJob implements ShouldQueue
{
    use Queueable;

    /** @var Order */
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        $order = $this->order->loadMissing(['user', 'items']);

        if (! $order->user) {
            Log::warning('SendOrderConfirmationJob: order has no user', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $order->user->notify(new OrderPlacedNotification($order));

        Log::info('Order confirmation queued/sent', [
            'order_id' => $order->id,
            'order_number' => $order->number,
            'user_id' => $order->user_id,
        ]);
    }
}
