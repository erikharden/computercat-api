<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank' => $this->rank ?? null,
            'score' => $this->score,
            'metadata' => $this->metadata,
            'submitted_at' => $this->submitted_at,
            'user' => [
                'id' => $this->user->id,
                'display_name' => $this->user->display_name ?? $this->user->name,
            ],
        ];
    }
}
