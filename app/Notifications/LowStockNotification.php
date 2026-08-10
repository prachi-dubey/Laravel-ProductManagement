<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Admin inbox: product stock is low after an order.
 * Sent from CheckLowStockJob (already queued).
 */
class LowStockNotification extends Notification
{
    use Queueable;

    /** @var Product */
    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'name' => $this->product->name,
            'stock' => $this->product->stock,
            'message' => "Low stock: {$this->product->name} ({$this->product->sku}) has {$this->product->stock} left.",
        ];
    }
}
