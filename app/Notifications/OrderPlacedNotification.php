<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Customer: email (MAIL_MAILER=log locally) + database inbox row.
 * Sent from SendOrderConfirmationJob (already queued).
 */
class OrderPlacedNotification extends Notification
{
    use Queueable;

    /** @var Order */
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
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
        $order = $this->order->loadMissing('items');

        $mail = (new MailMessage)
            ->subject('Order confirmed: '.$order->number)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Thanks for your order. Here is a quick summary:')
            ->line('Order: '.$order->number)
            ->line('Total: '.$order->total)
            ->line('Status: '.$order->status);

        foreach ($order->items as $item) {
            $mail->line("- {$item->product_name} × {$item->quantity} = {$item->line_total}");
        }

        return $mail->line('We will notify you when it ships.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'total' => $this->order->total,
            'message' => 'Your order '.$this->order->number.' was placed successfully.',
        ];
    }
}
