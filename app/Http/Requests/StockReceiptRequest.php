<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockReceiptRequest extends FormRequest
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
        $receipt = $this->route('stock_receipt');

        return [
            'receipt_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stock_receipts', 'receipt_number')->ignore($receipt),
            ],
            'source_id' => ['required', 'exists:stock_sources,id'],
            'received_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,posted'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.batch_number' => ['required', 'string', 'max:100'],
            'items.*.expired_at' => ['required', 'date'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
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
            'items.required' => 'Minimal satu item obat harus diisi.',
            'items.min' => 'Minimal satu item obat harus diisi.',
        ];
    }
}
