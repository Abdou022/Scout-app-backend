<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVilleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'sometimes|string|max:150|unique:villes,nom,' . $this->route('ville')->id,
        ];
    }
}
