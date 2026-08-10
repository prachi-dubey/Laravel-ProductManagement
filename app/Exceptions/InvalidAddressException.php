<?php

namespace App\Exceptions;

class InvalidAddressException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Address not found for this user.',
            422,
            'INVALID_ADDRESS',
            [
                'address_id' => ['Address not found for this user.'],
            ]
        );
    }
}
