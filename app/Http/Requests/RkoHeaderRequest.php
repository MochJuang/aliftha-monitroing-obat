<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RkoHeaderRequest extends FormRequest
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
        $rkoHeader = $this->route('rkoHeader') ?? $this->route('rko_header');

        return [
            'rko_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rko_headers', 'rko_number')->ignore($rkoHeader),
            ],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'status' => ['required', 'in:draft,submitted,approved,rejected'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'distinct', 'exists:medicines,id'],
            'items.*.planned_quantity' => ['required', 'integer', 'min:1'],
            'items.*.approved_quantity' => ['nullable', 'integer', 'min:0'],
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
            'items.*.medicine_id.distinct' => 'Obat pada detail RKO tidak boleh duplikat.',
        ];
    }
}
