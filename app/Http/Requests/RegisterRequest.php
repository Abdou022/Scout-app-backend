<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Inscription publique
    }

    public function rules(): array
    {
        return [
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'password'  => 'required|string|min:8|confirmed',
            'ville_id'  => 'required|exists:villes,id',
        ];
    }
}
