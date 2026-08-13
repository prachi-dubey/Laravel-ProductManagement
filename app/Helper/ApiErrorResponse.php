<?php

namespace App\Helper;

use App\Exceptions\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use JsonException as PhpJsonException;
use ParseError;
use Symfony\Component\HttpFoundation\Exception\JsonException as SymfonyJsonException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
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

        if ($e instanceof MethodNotAllowedHttpException) {
            return self::make(
                __('messages.errors.method_not_allowed'),
                Response::HTTP_METHOD_NOT_ALLOWED,
                'METHOD_NOT_ALLOWED'
            );
        }

        if (self::isSyntaxError($e)) {
            return self::make(
                __('messages.errors.syntax'),
                Response::HTTP_BAD_REQUEST,
                'BAD_REQUEST'
            );
        }

        if ($e instanceof AuthenticationException) {
            return self::make(__('messages.errors.unauthenticated'), Response::HTTP_UNAUTHORIZED, 'UNAUTHENTICATED');
        }

        if ($e instanceof AuthorizationException) {
            return self::make(
                $e->getMessage() ?: __('messages.errors.forbidden'),
                Response::HTTP_FORBIDDEN,
                'FORBIDDEN'
            );
        }

        if ($e instanceof ModelNotFoundException) {
            return self::make(__('messages.errors.not_found'), Response::HTTP_NOT_FOUND, 'NOT_FOUND');
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: self::defaultMessageForStatus($status);

            return self::make($message, $status, self::codeForStatus($status));
        }

        $message = config('app.debug')
            ? $e->getMessage()
            : __('messages.errors.server');

        return self::make($message, Response::HTTP_INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function make(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
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

    private static function isSyntaxError(Throwable $e): bool
    {
        if ($e instanceof PhpJsonException || $e instanceof SymfonyJsonException || $e instanceof ParseError) {
            return true;
        }

        $message = strtolower($e->getMessage());

        return str_contains($message, 'syntax error')
            || str_contains($message, 'could not decode request body');
    }

    private static function defaultMessageForStatus(int $status): string
    {
        $map = [
            Response::HTTP_BAD_REQUEST => __('messages.errors.bad_request'),
            Response::HTTP_UNAUTHORIZED => __('messages.errors.unauthenticated'),
            Response::HTTP_FORBIDDEN => __('messages.errors.forbidden'),
            Response::HTTP_NOT_FOUND => __('messages.errors.not_found'),
            Response::HTTP_METHOD_NOT_ALLOWED => __('messages.errors.method_not_allowed'),
            Response::HTTP_INTERNAL_SERVER_ERROR => __('messages.errors.server'),
        ];

        return $map[$status] ?? 'Request failed.';
    }

    private static function codeForStatus(int $status): string
    {
        $map = [
            Response::HTTP_BAD_REQUEST => 'BAD_REQUEST',
            Response::HTTP_UNAUTHORIZED => 'UNAUTHENTICATED',
            Response::HTTP_FORBIDDEN => 'FORBIDDEN',
            Response::HTTP_NOT_FOUND => 'NOT_FOUND',
            Response::HTTP_METHOD_NOT_ALLOWED => 'METHOD_NOT_ALLOWED',
            Response::HTTP_INTERNAL_SERVER_ERROR => 'SERVER_ERROR',
        ];

        return $map[$status] ?? 'HTTP_ERROR';
    }
}
