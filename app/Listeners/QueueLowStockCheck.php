<?php

namespace App\Listeners;

use App\Events\Order\OrderPlaced;
use App\Jobs\CheckLowStockJob;

/**
 * Bridges OrderPlaced → queued low-stock check.
 */
class QueueLowStockCheck
{
    public function handle(OrderPlaced $event): void
    {
        CheckLowStockJob::dispatch($event->order);
    }
}
