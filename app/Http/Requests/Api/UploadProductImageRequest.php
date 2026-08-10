<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is already behind auth:sanctum + admin; policy checked in controller.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // kilobytes = 2MB
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Please choose an image file.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Allowed types: jpg, jpeg, png, webp.',
            'image.max' => 'Image may not be greater than 2MB.',
        ];
    }
}
