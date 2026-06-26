<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'          => 'sometimes|string|max:150',
            'regiment_id'  => 'sometimes|exists:regiments,id',
            'chef_id'      => 'nullable|exists:users,id',
            'assistant_id' => 'nullable|exists:users,id',
        ];
    }
}
