<?php
  namespace App\Traits\User;

  use Illuminate\Http\UploadedFile;
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Database\Eloquent\Casts\Attribute;
  use App\Services\ImageOptimizerService;

  trait HasImageUser
  {

    /**
     * Update the user's profile photo.
     * 
     * @return void
     */
    public function deleteImage(): void
    {
        if (is_null($this->image)) {
            return;
        }

        Storage::disk(getDisk())->delete($this->image);

        $this->forceFill([
            'image' => '',
        ])->save();
    }

    /**
     * Get the URL to the user's profile photo.
     */
    public function imageUrl (): Attribute {
        return Attribute::get(function (): string {
            return $this->image
                    ? Storage::disk(getDisk())->url($this->image)
                    : $this->defaultCollectionImage();
        });
    }

    /**
     * Update the user's profile photo.
     * 
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  string  $storagePath
     */
    public function updateImage(UploadedFile $photo, $storagePath = 'users/images') {

        // check if storage path is valid
        ensureStorageDirectory($storagePath);

        if ($photo->getSize() > 200000) {
            $photo = ImageOptimizerService::optimizeUploadedFile($photo); // ya devuelve como webp
        } else if ($photo->getMimeType() != 'image/webp') {
            $photo = ImageOptimizerService::convertToWebP($photo); // ya devuelve como webp
        }

        tap($this->image, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'image' => $photo->storePublicly(
                    $storagePath, ['disk' => getDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk(getDisk())->delete($previous);
            }
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultCollectionImage()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

  }
  