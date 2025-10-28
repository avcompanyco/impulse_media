<?php

namespace App\Traits\Serie;

use App\Enums\Content\ContentStatus;
use App\Models\Serie;
use App\Services\BinacleService;
use Illuminate\Support\Facades\Auth;

trait HasPublishSerie
{
    public function publish(Serie $serie, array $data)
    {
        $serie->fill($data)->save();
        // verify all fields are filled
        if (! $serie->title || ! $serie->description || ! $serie->trailer_video || ! $serie->horizontal_image || ! $serie->vertical_image) {
            throw new \Exception(__('Cannot publish this serie'));
        }

        $content = $serie->content;

        $content->fill([
            'status' => ContentStatus::PUBLISHED->value,
        ])->save();

        // Log serie upload event
        $binacleService = app(BinacleService::class);
        $user = Auth::user();
        $binacleService->logSerieUpload($user, $serie->title);

        return $serie;
    }
}
