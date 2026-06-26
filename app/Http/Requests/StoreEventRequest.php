<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'lieu'        => 'nullable|string|max:255',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'cover_image' => 'nullable|image|max:4096',
            'type'        => 'required|in:ville,regiment',
            'ville_id'    => 'required|exists:villes,id',
            'regiment_id' => 'nullable|exists:regiments,id',
        ];
    }
}
