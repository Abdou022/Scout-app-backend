<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'    => 'required|string|max:100',
            'niveau' => 'required|integer|min:1',
            'image'  => 'nullable|image|max:2048',
        ];
    }
}
