<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'items is required.',
            'items.array'    => 'items must be an array.',
            'items.min'      => 'items must contain at least 1 item.',
            'items.*.product_id.required' => 'product_id is required for each item.',
            'items.*.product_id.exists'   => 'product_id must exist in products.',
            'items.*.quantity.required' => 'quantity is required for each item.',
            'items.*.quantity.min'      => 'quantity must be at least 1.',
        ];
    }
}
