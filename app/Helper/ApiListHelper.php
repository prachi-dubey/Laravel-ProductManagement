<?php

namespace App\Helper;

use Illuminate\Database\Eloquent\Builder;

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
     * Normalize page size from filters / query value (not from Request).
     */
    public static function perPage(int|string|null $perPage = null, int $default = 10, int $max = 50): int
    {
        $value = ($perPage === null || $perPage === '') ? $default : (int) $perPage;

        if ($value < 1) {
            $value = $default;
        }

        if ($value > $max) {
            $value = $max;
        }

        return $value;
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
                'has_more' => $paginator->hasMorePages(),
            ],
        ];
    }
}
