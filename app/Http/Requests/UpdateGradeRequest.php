<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'    => 'sometimes|string|max:100',
            'niveau' => 'sometimes|integer|min:1',
            'image'  => 'nullable|image|max:2048',
        ];
    }
}
