<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'          => 'required|string|max:150',
            'regiment_id'  => 'required|exists:regiments,id',
            'chef_id'      => 'nullable|exists:users,id',
            'assistant_id' => 'nullable|exists:users,id',
        ];
    }
}
