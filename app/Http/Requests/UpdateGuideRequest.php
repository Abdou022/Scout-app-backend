<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'        => 'sometimes|string|max:200',
            'contenu_html' => 'sometimes|string',
            'cover_image'  => 'nullable|image|max:4096',
            'category_id'  => 'sometimes|exists:categories,id',
        ];
    }
}
