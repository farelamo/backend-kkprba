<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|min:5|max:50',
            'short_title' => 'required|min:5|max:30',
            'description' => 'required|min:5',
            'categories' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi',
            'title.min' => 'Judul minimal 5 karakter',
            'title.max' => 'Judul maximal 50 karakter',
            'short_title' => 'Judul singkat wajib diisi',
            'short_title.min' => 'Judul singkat minimal 5 karakter',
            'short_title.max' => 'Judul singkat maximal 30 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'description.min' => 'Deskripsi minimal 5 karakter',
            'categories.required' => 'Kategori wajib diisi'
        ];
    }
}
