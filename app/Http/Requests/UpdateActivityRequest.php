<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'     => 'sometimes|string|max:200',
            'programme' => 'nullable|string',
            'date'      => 'sometimes|date',
            'lieu'      => 'nullable|string|max:255',
        ];
    }
}
