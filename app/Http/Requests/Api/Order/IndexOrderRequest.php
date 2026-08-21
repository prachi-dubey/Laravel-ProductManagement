<?php

namespace App\Http\Requests\Api\Order;

use App\Http\Traits\IndexQueryRules;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOrderRequest extends FormRequest
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
            'status' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in([
                    Order::STATUS_PENDING,
                    Order::STATUS_PAID,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_CANCELLED,
                ]),
            ],
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->prepareIndexBooleanFilters();
    }
}
