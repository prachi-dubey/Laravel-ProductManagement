<?php

namespace App\Helper;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApiListHelper
{
    /**
     * Apply whitelist sorting.
     *
     * @param  array<int, string>  $allowed
     */
    public static function applySort(
        Builder $builder,
        string $sortColumn,
        string $sortDirection,
        array $allowed,
        string $defaultColumn = 'created_at',
        string $defaultDirection = 'asc',
    ): Builder {
        $column = in_array($sortColumn, $allowed, true) ? $sortColumn : $defaultColumn;
        $direction = in_array(strtolower($sortDirection), ['asc', 'desc'], true)
            ? strtolower($sortDirection)
            : $defaultDirection;

        return $builder->orderBy($column, $direction);
    }

    /**
     * @return array{per_page: int}
     */
    public static function perPage(Request $request, int $default = 10, int $max = 50): array
    {
        $perPage = (int) $request->input('per_page', $default);

        if ($perPage < 1) {
            $perPage = $default;
        }

        if ($perPage > $max) {
            $perPage = $max;
        }

        return ['per_page' => $perPage];
    }

    /**
     * Standard list envelope with pagination details.
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
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
