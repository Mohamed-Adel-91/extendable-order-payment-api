<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use BenSampo\Enum\Rules\EnumValue;

class AdminUpdateOrderStatusRequest extends FormRequest
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
            'status' => [
                'required',
                new EnumValue(OrderStatus::class),
                'in:' . implode(',', [OrderStatus::SHIPPING, OrderStatus::DELIVERED]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'status is required.',
            'status.in'       => 'Admin can only set status to SHIPPING or DELIVERED.',
        ];
    }
}
