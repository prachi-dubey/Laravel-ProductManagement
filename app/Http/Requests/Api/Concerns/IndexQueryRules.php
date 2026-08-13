<?php

namespace App\Http\Requests\Api\Concerns;

trait IndexQueryRules
{
    /**
     * Shared list/filter query rules for index endpoints.
     *
     * @return array<string, mixed>
     */
    protected function indexQueryRules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'sort' => ['sometimes', 'string'],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    protected function prepareIndexBooleanFilters(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
