<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'provider_id' => $this->provider_id,
            'service_id' => $this->service_id,
            'start_time' => optional($this->start_time)->toIso8601String(),
            'end_time' => optional($this->end_time)->toIso8601String(),
            'status' => $this->status,
            'total_price' => $this->total_price,
            'customer' => new UserResource($this->whenLoaded('customer')),
            'provider' => new UserResource($this->whenLoaded('provider')),
            'service' => new ServiceResource($this->whenLoaded('service')),
        ];
    }
}


