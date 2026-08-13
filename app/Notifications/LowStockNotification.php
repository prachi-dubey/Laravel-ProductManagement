<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Admin: email (MAIL_MAILER=log locally) + database inbox row.
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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $product = $this->product;

        return (new MailMessage)
            ->subject('Low stock: '.$product->name)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('A product is at or below the stock threshold.')
            ->line('Product: '.$product->name)
            ->line('SKU: '.$product->sku)
            ->line('Stock left: '.$product->stock)
            ->line('Please restock this item.');
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
