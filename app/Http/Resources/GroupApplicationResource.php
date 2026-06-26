<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'group_id'   => $this->group_id,
            'statut'     => $this->statut,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relations chargées conditionnellement
            'user'       => new UserResource($this->whenLoaded('user')),
            'group'      => new GroupResource($this->whenLoaded('group')),
        ];
    }
}
