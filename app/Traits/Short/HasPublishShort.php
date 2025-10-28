<?php

namespace App\Traits\Short;

use App\Enums\Content\ContentStatus;
use App\Models\Short;
use App\Services\BinacleService;
use Illuminate\Support\Facades\Auth;

trait HasPublishShort
{
    public function publish(Short $short, array $data)
    {
        $short->fill($data)->save();
        // verify all fields are filled
        if (! $short->text_caption || ! $short->short_video) {
            throw new \Exception(__('Cannot publish this short'));
        }

        $content = $short->content;

        $content->fill([
            'status' => ContentStatus::PUBLISHED->value,
        ])->save();

        // Log short upload event
        $binacleService = app(BinacleService::class);
        $user = Auth::user();
        $binacleService->logShortUpload($user, $short->text_caption);

        return $short;
    }
}
