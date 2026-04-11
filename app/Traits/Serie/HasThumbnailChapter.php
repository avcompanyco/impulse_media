<?php

namespace App\Traits\Serie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\ImageOptimizerService;

trait HasThumbnailChapter
{

    /**
     * Update the vertical's profile photo.
     * 
     * @return void
     */
    public function deleteThumbnail(): void
    {
        if (is_null($this->thumbnail)) {
            return;
        }

        Storage::disk(getDisk())->delete($this->thumbnail);

        $this->forceFill([
            'thumbnail' => '',
        ])->save();
    }

    /**
     * Get the URL to the vertical's profile photo.
     */
    public function ThumbnailUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->thumbnail
                ? Storage::disk(getDisk())->url($this->thumbnail)
                : $this->defaultThumbnail();
        });
    }

    /**
     * Update the vertical's profile photo.
     * 
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  string  $storagePath
     */
    public function updateThumbnail(UploadedFile $photo, $storagePath = 'series')
    {
        $user_id_hash = hash('sha256', $this->user_id);
        $storagePath .= '/' . $user_id_hash . '/thumbnails';
        // check if storage path is valid
        ensureStorageDirectory($storagePath);

        if ($photo->getSize() > 200000) {
            $photo = ImageOptimizerService::optimizeUploadedFile($photo); // ya devuelve como webp
        } else if ($photo->getMimeType() != 'image/webp') {
            $photo = ImageOptimizerService::convertToWebP($photo); // ya devuelve como webp
        }

        tap($this->thumbnail, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'thumbnail' => $photo->storePublicly(
                    $storagePath,
                    ['disk' => getDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk(getDisk())->delete($previous);
            }
        });
    }

    /**
     * Get the default thumbnail URL if no thumbnail has been uploaded.
     * @return string
     */
    protected function defaultThumbnail()
    {
        $name = trim(collect(explode(' ', $this->title))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }

}
