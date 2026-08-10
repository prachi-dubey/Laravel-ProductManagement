<?php

namespace App\Support;

use App\Exceptions\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Builds the standard API error envelope used across /api/*.
 *
 * {
 *   "success": false,
 *   "message": "...",
 *   "error_code": "VALIDATION_ERROR",
 *   "errors": { "field": ["..."] }
 * }
 */
class ApiErrorResponse
{
    public static function fromThrowable(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null;
        }

        if ($e instanceof ApiException) {
            return self::make(
                $e->getMessage(),
                $e->status(),
                $e->errorCode(),
                $e->errors()
            );
        }

        if ($e instanceof ValidationException) {
            return self::make(
                $e->getMessage() ?: 'The given data was invalid.',
                $e->status,
                'VALIDATION_ERROR',
                $e->errors()
            );
        }

        if ($e instanceof AuthenticationException) {
            return self::make('Unauthenticated.', 401, 'UNAUTHENTICATED');
        }

        if ($e instanceof AuthorizationException) {
            return self::make(
                $e->getMessage() ?: 'This action is unauthorized.',
                403,
                'FORBIDDEN'
            );
        }

        if ($e instanceof ModelNotFoundException) {
            return self::make('Resource not found.', 404, 'NOT_FOUND');
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: self::defaultMessageForStatus($status);

            return self::make($message, $status, self::codeForStatus($status));
        }

        // Unexpected errors — hide details unless APP_DEBUG
        $message = config('app.debug')
            ? $e->getMessage()
            : 'Server error.';

        return self::make($message, 500, 'SERVER_ERROR');
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function make(
        string $message,
        int $status = 400,
        string $errorCode = 'API_ERROR',
        array $errors = []
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    private static function defaultMessageForStatus(int $status): string
    {
        $map = [
            400 => 'Bad request.',
            401 => 'Unauthenticated.',
            403 => 'Forbidden.',
            404 => 'Not found.',
            405 => 'Method not allowed.',
            429 => 'Too many requests.',
            500 => 'Server error.',
            503 => 'Service unavailable.',
        ];

        return $map[$status] ?? 'Request failed.';
    }

    private static function codeForStatus(int $status): string
    {
        $map = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHENTICATED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            429 => 'TOO_MANY_REQUESTS',
            500 => 'SERVER_ERROR',
            503 => 'SERVICE_UNAVAILABLE',
        ];

        return $map[$status] ?? 'HTTP_ERROR';
    }
}
