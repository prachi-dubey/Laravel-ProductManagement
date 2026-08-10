<?php

namespace App\Exceptions;

class ProductImageMissingException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Product has no image to delete.',
            422,
            'PRODUCT_IMAGE_MISSING',
            [
                'image' => ['Product has no image to delete.'],
            ]
        );
    }
}
