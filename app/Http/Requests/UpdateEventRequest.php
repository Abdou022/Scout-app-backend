<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'       => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'date_debut'  => 'sometimes|date',
            'date_fin'    => 'sometimes|date|after_or_equal:date_debut',
            'lieu'        => 'nullable|string|max:255',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'cover_image' => 'nullable|image|max:4096',
        ];
    }
}
