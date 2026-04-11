<?php

namespace App\Traits\Serie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\ImageOptimizerService;

trait HasHorizontalImageSerie
{

    /**
     * Update the vertical's profile photo.
     * 
     * @return void
     */
    public function deleteHorizontalImage(): void
    {
        if (is_null($this->horizontal_image)) {
            return;
        }

        Storage::disk(getDisk())->delete($this->horizontal_image);

        $this->forceFill([
            'horizontal_image' => '',
        ])->save();
    }

    /**
     * Get the URL to the vertical's profile photo.
     */
    public function HorizontalImageUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->horizontal_image) {
                return $this->defaultHorizontalImage();
            }

            $disk = Storage::disk(getDisk());

            try {
                return $disk->temporaryUrl($this->horizontal_image, now()->addDay());
            } catch (\Throwable $e) {
                return $disk->url($this->horizontal_image);
            }
        });
    }

    /**
     * Update the vertical's profile photo.
     * 
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  string  $storagePath
     */
    public function updateHorizontalImage(UploadedFile $photo, $storagePath = 'series')
    {
        $user_id_hash = hash('sha256', $this->user_id);
        $storagePath .= '/' . $user_id_hash . '/horizontal_images';
        // check if storage path is valid
        ensureStorageDirectory($storagePath);

        if ($photo->getSize() > 200000) {
            $photo = ImageOptimizerService::optimizeUploadedFile($photo); // ya devuelve como webp
        } else if ($photo->getMimeType() != 'image/webp') {
            $photo = ImageOptimizerService::convertToWebP($photo); // ya devuelve como webp
        }

        tap($this->horizontal_image, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'horizontal_image' => $photo->storePublicly(
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
     * Get the default vertical photo URL if no profile photo has been uploaded.
     * @return string
     */
    protected function defaultHorizontalImage()
    {
        $name = trim(collect(explode(' ', $this->title))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }

}
