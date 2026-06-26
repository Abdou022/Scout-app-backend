<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'nom'          => $this->nom,
            'regiment_id'  => $this->regiment_id,
            'chef_id'      => $this->chef_id,
            'assistant_id' => $this->assistant_id,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,

            // Relations chargées conditionnellement
            'regiment'     => new RegimentResource($this->whenLoaded('regiment')),
            'chef'         => new UserResource($this->whenLoaded('chef')),
            'assistant'    => new UserResource($this->whenLoaded('assistant')),
            'members'      => UserResource::collection($this->whenLoaded('members')),
        ];
    }
}
