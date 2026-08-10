<?php

namespace App\Exceptions;

class ProductInUseException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Cannot delete product that appears on existing orders.',
            422,
            'PRODUCT_IN_USE',
            [
                'product' => ['Cannot delete product that appears on existing orders.'],
            ]
        );
    }
}
