<?php

namespace App\Traits\Serie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

        Storage::disk($this->HorizontalPhotoDisk())->delete($this->horizontal_image);

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
            return $this->horizontal_image
                ? Storage::disk($this->HorizontalPhotoDisk())->url($this->horizontal_image)
                : $this->defaultHorizontalImage();
        });
    }

    /**
     * Update the vertical's profile photo.
     * 
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  string  $storagePath
     */
    public function updateHorizontalImage(UploadedFile $photo, $storagePath = 'series/horizontal_images')
    {
        tap($this->horizontal_image, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'horizontal_image' => $photo->storePublicly(
                    $storagePath,
                    ['disk' => $this->HorizontalPhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->HorizontalPhotoDisk())->delete($previous);
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

    /**
     * Get the disk that vertical photos should be stored on.
     *
     * @return string
     */
    protected function HorizontalPhotoDisk()
    {
        return isset($_ENV['FILESYSTEM_DISK']) ? 's3' : 'public';
    }
}
