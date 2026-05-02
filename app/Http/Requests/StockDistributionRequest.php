<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockDistributionRequest extends FormRequest
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
        $distribution = $this->route('stock_distribution');

        return [
            'distribution_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stock_distributions', 'distribution_number')->ignore($distribution),
            ],
            'destination_id' => ['required', 'exists:distribution_destinations,id'],
            'distributed_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,posted'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'distinct', 'exists:medicines,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
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
            'items.*.medicine_id.distinct' => 'Setiap obat cukup diinput satu kali. Tambah jumlahnya bila perlu.',
        ];
    }
}
