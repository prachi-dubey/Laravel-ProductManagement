<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Single API/domain exception — use static helpers for each error case.
 */
class ApiException extends Exception
{
    /** @var int */
    protected $status;

    /** @var string */
    protected $errorCode;

    /** @var array<string, mixed> */
    protected $errors;

    /**
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        string $message,
        int $status = 422,
        string $errorCode = 'API_ERROR',
        array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->status = $status;
        $this->errorCode = $errorCode;
        $this->errors = $errors;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public static function invalidAddress(): self
    {
        $message = __('messages.orders.invalid_address');

        return new self(
            $message,
            422,
            'INVALID_ADDRESS',
            ['address_id' => [$message]]
        );
    }

    public static function productUnavailable(): self
    {
        $message = __('messages.orders.product_unavailable');

        return new self(
            $message,
            422,
            'PRODUCT_UNAVAILABLE',
            ['items' => [$message]]
        );
    }

    public static function insufficientStock(string $productName, int $available): self
    {
        $message = __('messages.orders.insufficient_stock', [
            'name' => $productName,
            'available' => $available,
        ]);

        return new self(
            $message,
            422,
            'INSUFFICIENT_STOCK',
            ['items' => [$message]]
        );
    }

    public static function productInUse(): self
    {
        $message = __('messages.products.in_use');

        return new self(
            $message,
            422,
            'PRODUCT_IN_USE',
            ['product' => [$message]]
        );
    }

    public static function productImageMissing(): self
    {
        $message = __('messages.products.image_missing');

        return new self(
            $message,
            422,
            'PRODUCT_IMAGE_MISSING',
            ['image' => [$message]]
        );
    }

    public static function categoryInUse(): self
    {
        $message = __('messages.categories.in_use');

        return new self(
            $message,
            422,
            'CATEGORY_IN_USE',
            ['category' => [$message]]
        );
    }
}
