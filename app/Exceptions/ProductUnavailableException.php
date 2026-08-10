<?php

namespace App\Exceptions;

class ProductUnavailableException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'One or more products are unavailable.',
            422,
            'PRODUCT_UNAVAILABLE',
            [
                'items' => ['One or more products are unavailable.'],
            ]
        );
    }
}
