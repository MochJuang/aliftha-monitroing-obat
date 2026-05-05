<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $stockMutation = $this->route('stockMutation') ?? $this->route('stock_mutation');

        return [
            'mutation_number' => ['required', 'string', 'max:50', Rule::unique('stock_mutations', 'mutation_number')->ignore($stockMutation)],
            'mutation_date' => ['required', 'date'],
            'mutation_type' => ['required', Rule::in(['MASUK', 'KELUAR'])],
            'distribution_destination_id' => ['nullable', 'exists:distribution_destinations,id'],
            'reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'distinct', 'exists:medicines,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
