<?php

namespace App\Helper;

use App\Exceptions\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

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
                $e->getMessage() ?: __('messages.errors.validation'),
                $e->status,
                'VALIDATION_ERROR',
                $e->errors()
            );
        }

        if ($e instanceof AuthenticationException) {
            return self::make(__('messages.errors.unauthenticated'), 401, 'UNAUTHENTICATED');
        }

        if ($e instanceof AuthorizationException) {
            return self::make(
                $e->getMessage() ?: __('messages.errors.forbidden'),
                403,
                'FORBIDDEN'
            );
        }

        if ($e instanceof ModelNotFoundException) {
            return self::make(__('messages.errors.not_found'), 404, 'NOT_FOUND');
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: self::defaultMessageForStatus($status);

            return self::make($message, $status, self::codeForStatus($status));
        }

        $message = config('app.debug')
            ? $e->getMessage()
            : __('messages.errors.server');

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
            400 => __('messages.errors.bad_request'),
            401 => __('messages.errors.unauthenticated'),
            403 => __('messages.errors.forbidden'),
            404 => __('messages.errors.not_found'),
            500 => __('messages.errors.server'),
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
            500 => 'SERVER_ERROR',
        ];

        return $map[$status] ?? 'HTTP_ERROR';
    }
}
