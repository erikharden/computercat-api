<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AchievementDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->when(! $this->is_secret, $this->description, '???'),
            'icon' => $this->icon,
            'is_secret' => $this->is_secret,
            'sort_order' => $this->sort_order,
        ];
    }
}
