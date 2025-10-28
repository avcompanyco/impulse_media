<?php

namespace App\Traits\Serie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasVerticalImageSerie
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

        Storage::disk($this->VerticalPhotoDisk())->delete($this->vertical_image);

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
                ? Storage::disk($this->VerticalPhotoDisk())->url($this->vertical_image)
                : $this->defaultVerticalImage();
        });
    }

    /**
     * Update the vertical's profile photo.
     * 
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  string  $storagePath
     */
    public function updateVerticalImage(UploadedFile $photo, $storagePath = 'series/vertical_images')
    {
        tap($this->vertical_image, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'vertical_image' => $photo->storePublicly(
                    $storagePath,
                    ['disk' => $this->VerticalPhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->VerticalPhotoDisk())->delete($previous);
            }
        });
    }

    /**
     * Get the default vertical photo URL if no profile photo has been uploaded.
     * @return string
     */
    protected function defaultVerticalImage()
    {
        $name = trim(collect(explode(' ', $this->title))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that vertical photos should be stored on.
     *
     * @return string
     */
    protected function VerticalPhotoDisk()
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : 'public';
    }
}
