<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Traits\CategoryIdsRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared store, update, and category-sync validation for products.
 */
class SaveProductRequest extends FormRequest
{
    use CategoryIdsRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->routeIs('products.categories.sync')) {
            return $this->categoryIdsRules(required: true);
        }

        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;
        $updating = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return array_merge(
            $this->categoryIdsRules(required: ! $updating),
            [
                'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:160'],
                'slug' => [
                    $updating ? 'sometimes' : 'nullable',
                    'nullable',
                    'string',
                    'max:180',
                    $updating
                        ? Rule::unique('products', 'slug')->ignore($productId)
                        : Rule::unique('products', 'slug'),
                ],
                'description' => [$updating ? 'sometimes' : 'nullable', 'nullable', 'string'],
                'price' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0'],
                'stock' => [$updating ? 'sometimes' : 'required', 'integer', 'min:0'],
                'is_active' => ['sometimes', 'boolean'],
                'image' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'remove_image' => ['sometimes', 'boolean'],
            ]
        );
    }

    protected function prepareForValidation(): void
    {
        $categoryIds = $this->input('category_ids');

        if (is_string($categoryIds)) {
            $decoded = json_decode($categoryIds, true);
            if (is_array($decoded)) {
                $this->merge(['category_ids' => $decoded]);
            }
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }

        if ($this->has('remove_image')) {
            $this->merge([
                'remove_image' => filter_var($this->input('remove_image'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
