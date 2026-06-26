<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'          => 'required|exists:users,id',
            'attendable_type'  => 'required|in:App\Models\Event,App\Models\Activity',
            'attendable_id'    => 'required|integer',
            'statut'           => 'required|in:present,absent,en_attente',
            'date_pointage'    => 'required|date',
        ];
    }
}
