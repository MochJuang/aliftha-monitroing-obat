<?php

namespace App\Http\Requests;

use App\Models\DistributionDestination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistributionDestinationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var DistributionDestination|null $destination */
        $destination = $this->route('distribution_destination');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('distribution_destinations', 'code')->ignore($destination),
            ],
            'name' => ['required', 'string', 'max:150'],
            'destination_type' => ['required', 'in:puskesmas,klinik,bidan,lainnya'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
