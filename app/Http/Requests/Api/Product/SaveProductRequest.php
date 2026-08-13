<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\Api\Concerns\CategoryIdsRules;
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
                'sku' => [
                    $updating ? 'sometimes' : 'required',
                    'string',
                    'max:60',
                    $updating
                        ? Rule::unique('products', 'sku')->ignore($productId)
                        : Rule::unique('products', 'sku'),
                ],
                'description' => [$updating ? 'sometimes' : 'nullable', 'nullable', 'string'],
                'price' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0'],
                'stock' => [$updating ? 'sometimes' : 'required', 'integer', 'min:0'],
                'image_path' => [$updating ? 'sometimes' : 'nullable', 'nullable', 'string', 'max:255'],
                'is_active' => ['sometimes', 'boolean'],
            ]
        );
    }
}
