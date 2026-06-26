<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'titre'      => $this->titre,
            'paroles'    => $this->paroles,
            'audio_url'  => $this->audio_url
                ? asset('storage/' . $this->audio_url)
                : null,
            'categorie'  => $this->categorie,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Créateur chargé conditionnellement
            'creator'    => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
