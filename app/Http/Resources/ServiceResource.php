<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration' => $this->duration,
            'price' => $this->price,
            'is_published' => $this->is_published,
            'category_id' => $this->category_id,
            'provider_id' => $this->provider_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'provider' => new UserResource($this->whenLoaded('provider')),
        ];
    }
}


