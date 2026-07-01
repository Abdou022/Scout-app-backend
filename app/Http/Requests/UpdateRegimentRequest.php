<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegimentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'      => 'sometimes|string|max:150',
            'ville_id' => 'sometimes|exists:villes,id',
            //'chef_id'  => 'nullable|exists:users,id',
        ];
    }
}
