<?php

namespace App\Http\Resources\Search;

use App\Http\Resources\Content\ContentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilterSearchContentResource extends JsonResource
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
            'contentable' => $this->contentable,
        ];
    }
}
