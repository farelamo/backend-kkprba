<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegulationCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|min:1|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.min' => 'Nama minimal 1 karakter',
            'name.max' => 'Nama kategori maximal 20 karakter',
        ];
    }
}
