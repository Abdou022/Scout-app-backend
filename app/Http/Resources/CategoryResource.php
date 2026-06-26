<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nom'        => $this->nom,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Guides de cette catégorie chargés conditionnellement
            'guides'     => GuideResource::collection($this->whenLoaded('guides')),
        ];
    }
}
