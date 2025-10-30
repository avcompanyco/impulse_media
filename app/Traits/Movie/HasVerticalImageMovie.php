<?php

namespace App\Traits\Movie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\ImageOptimizerService;

trait HasVerticalImageMovie
{

    /**
     * Update the vertical's profile photo.
     * 
     * @return void
     */
    public function deleteVerticalImage(): void
    {
        if (is_null($this->vertical_image)) {
            return;
        }

        Storage::disk(getDisk())->delete($this->vertical_image);

        $this->forceFill([
            'vertical_image' => '',
        ])->save();
    }

    /**
     * Get the URL to the vertical's profile photo.
     */
    public function verticalImageUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->vertical_image
                ? Storage::disk(getDisk())->url($this->vertical_image)
                : $this->defaultVerticalImage();
        });
    }

    /**
     * Update the vertical's profile photo.
     * 
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  string  $storagePath
     */
    public function updateVerticalImage(UploadedFile $photo, $storagePath = 'movies/vertical_images')
    {
        if ($photo->getSize() > 200000) {
            $photo = ImageOptimizerService::optimizeUploadedFile($photo); // ya devuelve como webp
        } else if ($photo->getMimeType() != 'image/webp') {
            $photo = ImageOptimizerService::convertToWebP($photo); // ya devuelve como webp
        }

        tap($this->vertical_image, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'vertical_image' => $photo->storePublicly(
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
    protected function defaultVerticalImage()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }

}
