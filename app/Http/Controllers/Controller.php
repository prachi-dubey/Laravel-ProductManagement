<?php

namespace App\Http\Controllers;

use App\Helper\ApiListHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Standard API success JSON envelope.
     *
     * @param  mixed  $data
     */
    protected function success(string $message, $data = null, int $status = Response::HTTP_OK): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data instanceof JsonResource
                ? $data->resolve(request())
                : $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * Standard API paginated JSON envelope.
     *
     * @param  mixed  $resourceCollection
     */
    protected function paginated($resourceCollection, string $message): JsonResponse
    {
        return response()->json(
            ApiListHelper::paginatedResponse($resourceCollection, $message)
        );
    }
}
