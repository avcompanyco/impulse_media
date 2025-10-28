<?php

namespace App\Http\Resources\Binacle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BinacleCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = BinacleResource::class;
    
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

}
