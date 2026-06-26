<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VilleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nom'        => $this->nom,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relations chargées conditionnellement
            'regiments'  => RegimentResource::collection($this->whenLoaded('regiments')),
            'users'      => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
