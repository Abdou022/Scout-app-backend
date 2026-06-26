<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'     => 'required|string|max:200',
            'paroles'   => 'nullable|string',
            'audio'     => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480',
            'categorie' => 'nullable|string|max:100',
        ];
    }
}
