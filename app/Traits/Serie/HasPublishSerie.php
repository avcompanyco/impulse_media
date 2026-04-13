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
        // verify required fields are filled (trailer is optional)
        $missing = [];
        if (!$serie->title) $missing[] = 'Title';
        if (!$serie->description) $missing[] = 'Description';
        if (!$serie->horizontal_image) $missing[] = 'Horizontal Image';
        if (!$serie->vertical_image) $missing[] = 'Vertical Image';
        if (!empty($missing)) {
            throw new \Exception(__('Cannot publish this serie. Missing: ') . implode(', ', $missing));
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
