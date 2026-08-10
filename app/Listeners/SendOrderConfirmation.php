<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendOrderConfirmationJob;

/**
 * Bridges OrderPlaced → queued confirmation job.
 */
class SendOrderConfirmation
{
    public function handle(OrderPlaced $event): void
    {
        SendOrderConfirmationJob::dispatch($event->order);
    }
}
