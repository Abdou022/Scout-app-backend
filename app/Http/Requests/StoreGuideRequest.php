<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'        => 'required|string|max:200',
            'contenu_html' => 'required|string',
            'cover_image'  => 'nullable|image|max:4096',
            'category_id'  => 'required|exists:categories,id',
        ];
    }
}
