<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAchievementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->achievementDefinition->slug,
            'name' => $this->achievementDefinition->name,
            'description' => $this->achievementDefinition->description,
            'icon' => $this->achievementDefinition->icon,
            'unlocked_at' => $this->unlocked_at,
        ];
    }
}
