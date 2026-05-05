<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FundingSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fundingSource = $this->route('fundingSource') ?? $this->route('funding_source');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('funding_sources', 'code')->ignore($fundingSource),
            ],
            'name' => ['required', 'string', 'max:150'],
            'source_type' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
