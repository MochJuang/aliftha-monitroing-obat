<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RkoHeaderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(function (array $item) {
                if (array_key_exists('estimated_unit_price', $item)) {
                    $item['estimated_unit_price'] = $this->normalizeRupiah($item['estimated_unit_price']);
                }

                return $item;
            })
            ->all();

        $this->merge([
            'total_budget' => $this->normalizeRupiah($this->input('total_budget')),
            'items' => $items,
        ]);
    }

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
            'funding_source_id' => ['required', 'exists:funding_sources,id'],
            'total_budget' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,submitted'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'distinct', 'exists:medicines,id'],
            'items.*.planned_quantity' => ['required', 'integer', 'min:1'],
            'items.*.estimated_unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.priority' => ['required', 'in:tinggi,sedang,rendah'],
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

    private function normalizeRupiah(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) floor((float) $value);
        }

        return (int) preg_replace('/[^\d]/', '', (string) ($value ?? 0));
    }
}
