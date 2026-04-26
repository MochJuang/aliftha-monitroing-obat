<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
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
            'adjustment_number' => ['required', 'string', 'max:50', Rule::unique('stock_adjustments', 'adjustment_number')],
            'adjustment_date' => ['required', 'date'],
            'adjustment_type' => ['required', 'in:opname,koreksi,expired,rusak'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_id' => ['required', 'distinct', 'exists:medicine_batches,id'],
            'items.*.actual_qty' => ['required', 'integer', 'min:0'],
            'items.*.reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Minimal satu batch harus dipilih untuk penyesuaian.',
            'items.min' => 'Minimal satu batch harus dipilih untuk penyesuaian.',
            'items.*.batch_id.distinct' => 'Satu batch hanya boleh muncul satu kali dalam adjustment.',
        ];
    }
}
