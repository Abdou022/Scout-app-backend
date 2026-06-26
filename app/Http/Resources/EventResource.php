<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'titre'       => $this->titre,
            'description' => $this->description,
            'date_debut'  => $this->date_debut,
            'date_fin'    => $this->date_fin,
            'lieu'        => $this->lieu,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'cover_image' => $this->cover_image
                ? asset('storage/' . $this->cover_image)
                : null,
            'type'        => $this->type,
            'ville_id'    => $this->ville_id,
            'regiment_id' => $this->regiment_id,
            'created_by'  => $this->created_by,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            // Relations chargées conditionnellement
            'ville'       => new VilleResource($this->whenLoaded('ville')),
            'regiment'    => new RegimentResource($this->whenLoaded('regiment')),
            'creator'     => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
