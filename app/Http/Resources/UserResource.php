<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'prenom'      => $this->prenom,
            'email'       => $this->email,
            'telephone'   => $this->telephone,
            'role'        => $this->role,
            'profile_pic' => $this->profile_pic
                ? asset('storage/' . $this->profile_pic)
                : null,
            'ville_id'    => $this->ville_id,
            'regiment_id' => $this->regiment_id,
            'group_id'    => $this->group_id,
            'grade_id'    => $this->grade_id,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            // Relations chargées conditionnellement
            'ville'       => new VilleResource($this->whenLoaded('ville')),
            'regiment'    => new RegimentResource($this->whenLoaded('regiment')),
            'group'       => new GroupResource($this->whenLoaded('group')),
            'grade'       => new GradeResource($this->whenLoaded('grade')),
        ];
    }
}
