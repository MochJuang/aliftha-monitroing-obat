<?php

namespace App\Http\Requests;

use App\Models\MedicineCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineCategoryRequest extends FormRequest
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
        /** @var MedicineCategory|null $category */
        $category = $this->route('medicineCategory') ?? $this->route('medicine_category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('medicine_categories', 'name')->ignore($category),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
