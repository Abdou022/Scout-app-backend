<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'     => 'sometimes|string|max:200',
            'paroles'   => 'nullable|string',
            'categorie' => 'nullable|string|max:100',
        ];
    }
}
