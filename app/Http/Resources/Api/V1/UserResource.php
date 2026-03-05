<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name ?? $this->name,
            'is_anonymous' => $this->is_anonymous,
            'email' => $this->when(! $this->is_anonymous, $this->email),
            'created_at' => $this->created_at,
        ];
    }
}
