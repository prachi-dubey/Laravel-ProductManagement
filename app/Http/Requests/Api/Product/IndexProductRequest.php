<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\Api\Concerns\IndexQueryRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
    use IndexQueryRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->indexQueryRules(), [
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->prepareIndexBooleanFilters();
    }
}
