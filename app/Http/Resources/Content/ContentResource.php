<?php

namespace App\Http\Resources\Content;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\Content\ContentType;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->type == ContentType::SHORT) {
            $title = $this->contentable->text_caption;
        } else {
            $title = $this->contentable->title;
        }

        return [
            // ...parent::toArray($request),
            'id' => $this->id,
            'username' => $this->user->username,
            'title' => $title,
            'url' => $this->contentable->url_path,
            'type' => $this->type_formatted,
            'status' => $this->status_formatted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
