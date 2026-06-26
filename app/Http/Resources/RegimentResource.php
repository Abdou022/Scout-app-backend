<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegimentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nom'        => $this->nom,
            'ville_id'   => $this->ville_id,
            'chef_id'    => $this->chef_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relations chargées conditionnellement
            'ville'      => new VilleResource($this->whenLoaded('ville')),
            'chef'       => new UserResource($this->whenLoaded('chef')),
            'groups'     => GroupResource::collection($this->whenLoaded('groups')),
        ];
    }
}
