<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RkoApprovalRequest extends FormRequest
{
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
}
