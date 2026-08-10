<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApiListQuery
{
    /**
     * Apply whitelist sorting.
     * Examples: sort=price  |  sort=-created_at
     *
     * @param  array<int, string>  $allowed
     */
    public static function applySort(Builder $query, Request $request, array $allowed, string $default = '-created_at'): Builder
    {
        $sort = (string) $request->query('sort', $default);
        $direction = 'asc';

        if (str_starts_with($sort, '-')) {
            $direction = 'desc';
            $sort = substr($sort, 1);
        }

        if (! in_array($sort, $allowed, true)) {
            $sort = ltrim($default, '-');
            $direction = str_starts_with($default, '-') ? 'desc' : 'asc';
        }

        return $query->orderBy($sort, $direction);
    }

    /**
     * @return array{per_page: int}
     */
    public static function perPage(Request $request, int $default = 10, int $max = 50): array
    {
        $perPage = (int) $request->query('per_page', $default);

        if ($perPage < 1) {
            $perPage = $default;
        }

        if ($perPage > $max) {
            $perPage = $max;
        }

        return ['per_page' => $perPage];
    }

    /**
     * Standard list envelope with pagination meta.
     *
     * @param  mixed  $resourceCollection
     * @return array<string, mixed>
     */
    public static function paginatedResponse($resourceCollection, string $message): array
    {
        $paginator = $resourceCollection->resource;

        return [
            'success' => true,
            'message' => $message,
            'data' => $resourceCollection->collection,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }
}
