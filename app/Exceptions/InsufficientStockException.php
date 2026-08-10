<?php

namespace App\Exceptions;

class InsufficientStockException extends ApiException
{
    public function __construct(string $productName, int $available)
    {
        parent::__construct(
            "Insufficient stock for {$productName} (available: {$available}).",
            422,
            'INSUFFICIENT_STOCK',
            [
                'items' => ["Insufficient stock for {$productName} (available: {$available})."],
            ]
        );
    }
}
