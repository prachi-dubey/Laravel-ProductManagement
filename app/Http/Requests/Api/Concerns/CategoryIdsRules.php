<?php

namespace App\Http\Requests\Api\Concerns;

trait CategoryIdsRules
{
    /**
     * @return array<string, mixed>
     */
    protected function categoryIdsRules(bool $required = true): array
    {
        $presence = $required ? ['required'] : ['sometimes'];

        return [
            'category_ids' => array_merge($presence, ['array', 'min:1']),
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
