<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegimentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'      => 'required|string|max:150',
            'ville_id' => 'required|exists:villes,id',
            'chef_id'  => 'nullable|exists:users,id',
        ];
    }
}
