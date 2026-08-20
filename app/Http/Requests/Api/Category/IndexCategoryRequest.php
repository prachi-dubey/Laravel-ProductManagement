<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Traits\IndexQueryRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexCategoryRequest extends FormRequest
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
        return $this->indexQueryRules();
    }

    protected function prepareForValidation(): void
    {
        $this->prepareIndexBooleanFilters();
    }
}
