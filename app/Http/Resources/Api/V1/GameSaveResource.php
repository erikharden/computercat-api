<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameSaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'save_key' => $this->save_key,
            'data' => $this->data,
            'version' => $this->version,
            'checksum' => $this->checksum,
            'saved_at' => $this->saved_at,
        ];
    }
}
