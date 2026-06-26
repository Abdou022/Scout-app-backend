<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'titre'        => $this->titre,
            'contenu_html' => $this->contenu_html,
            'cover_image'  => $this->cover_image
                ? asset('storage/' . $this->cover_image)
                : null,
            'category_id'  => $this->category_id,
            'created_by'   => $this->created_by,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,

            // Relations chargées conditionnellement
            'category'     => new CategoryResource($this->whenLoaded('category')),
            'creator'      => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
