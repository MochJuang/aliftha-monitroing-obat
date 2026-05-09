<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RkoApprovalRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(function (array $item) {
                if (array_key_exists('approved_unit_price', $item)) {
                    $item['approved_unit_price'] = $this->normalizeRupiah($item['approved_unit_price']);
                }

                return $item;
            })
            ->all();

        $this->merge([
            'items' => $items,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isApproved = $this->input('status') === 'approved';

        return [
            'status' => ['required', 'in:approved,rejected'],
            'approved_at' => [Rule::requiredIf($isApproved), 'nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'distinct', 'exists:rko_details,id'],
            'items.*.approved_quantity' => [Rule::requiredIf($isApproved), 'nullable', 'integer', 'min:0'],
            'items.*.approved_unit_price' => [Rule::requiredIf($isApproved), 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'approved_at.required' => 'Tanggal persetujuan wajib diisi saat RKO disetujui.',
            'items.*.approved_quantity.required' => 'Jumlah disetujui wajib diisi untuk setiap item saat RKO disetujui.',
            'items.*.approved_unit_price.required' => 'Harga satuan disetujui wajib diisi untuk setiap item saat RKO disetujui.',
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
