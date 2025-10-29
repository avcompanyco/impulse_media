<?php
  namespace App\Traits\Category;

  use Illuminate\Http\UploadedFile;
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Database\Eloquent\Casts\Attribute;

  trait HasImageCategory
  {

    /**
     * Update the category's profile photo.
     * 
     * @return void
     */
    public function deleteImage(): void
    {
        if (is_null($this->image)) {
            return;
        }

        Storage::disk($this->CollectionPhotoDisk())->delete($this->image);

        $this->forceFill([
            'image' => '',
        ])->save();
    }

    /**
     * Get the URL to the category's profile photo.
     */
    public function imageUrl (): Attribute {
        return Attribute::get(function (): string {
            return $this->image
                    ? Storage::disk($this->CollectionPhotoDisk())->url($this->image)
                    : $this->defaultCollectionImage();
        });
    }

    /**
     * Update the category's profile photo.
     * 
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  string  $storagePath
     */
    public function updateImage(UploadedFile $photo, $storagePath = 'categories/images') {
        tap($this->image, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'image' => $photo->storePublicly(
                    $storagePath, ['disk' => $this->CollectionPhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->CollectionPhotoDisk())->delete($previous);
            }
        });
    }

    /**
     * Get the default category photo URL if no profile photo has been uploaded.
     * @return string
     */
    protected function defaultCollectionImage()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

     /**
     * Get the disk that category photos should be stored on.
     *
     * @return string
     */
    protected function CollectionPhotoDisk()
    {
        return isset($_ENV['FILESYSTEM_DISK']) ? 's3' : 'public';
    }
  }
  