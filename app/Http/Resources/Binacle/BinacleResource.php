<?php

namespace App\Http\Resources\Binacle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BinacleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
            ]
        ];
    }
}
