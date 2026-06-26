<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'group_id'   => $this->group_id,
            'titre'      => $this->titre,
            'programme'  => $this->programme,
            'date'       => $this->date,
            'lieu'       => $this->lieu,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relations chargées conditionnellement
            'group'      => new GroupResource($this->whenLoaded('group')),
            'creator'    => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
