<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared store + update validation for categories.
 */
class SaveCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = is_object($category) ? $category->id : $category;
        $updating = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:120'],
            'slug' => [
                $updating ? 'sometimes' : 'nullable',
                'nullable',
                'string',
                'max:140',
                $updating
                    ? Rule::unique('categories', 'slug')->ignore($categoryId)
                    : Rule::unique('categories', 'slug'),
            ],
            'description' => [$updating ? 'sometimes' : 'nullable', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
