<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id'   => 'required|exists:groups,id',
            'titre'      => 'required|string|max:200',
            'programme'  => 'nullable|string',
            'date'       => 'required|date',
            'lieu'       => 'nullable|string|max:255',
        ];
    }
}
