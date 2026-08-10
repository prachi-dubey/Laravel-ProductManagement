<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Base domain/API exception — rendered as consistent JSON for /api/*.
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
}
