<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'attendable_type'  => $this->attendable_type,
            'attendable_id'    => $this->attendable_id,
            'statut'           => $this->statut,
            'date_pointage'    => $this->date_pointage,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,

            // Relations chargées conditionnellement
            'user'             => new UserResource($this->whenLoaded('user')),
        ];
    }
}
