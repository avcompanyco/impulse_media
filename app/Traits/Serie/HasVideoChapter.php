<?php

namespace App\Traits\Serie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\File;

trait HasVideoChapter
{

    /**
     * Update the chapter video's profile video.
     * 
     * @return void
     */
    public function deleteChapterVideo(): void
    {
        if (is_null($this->chapter_video)) {
            return;
        }

        Storage::disk(getDisk())->delete($this->chapter_video);

        $this->forceFill([
            'chapter_video' => '',
        ])->save();
    }

    /**
     * Get the URL to the chapter video's profile video.
     */
    public function chapterVideoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->chapter_video) {
                return $this->defaultChapterVideo();
            }
    
            $disk = Storage::disk(getDisk());
    
            try {
                // URL temporal válida por 1 hora
                return $disk->temporaryUrl($this->chapter_video, now()->addHour());
            } catch (\Throwable $e) {
                // fallback a URL normal si el disco no soporta temporaryUrl
                return $disk->url($this->chapter_video);
            }
        });
    }

    /**
     * Update the chapter video's profile video.
     * 
     * @param  $video
     * @param  string  $storagePath
     */
    public function updateChapterVideo($video, $storagePath = 'series')
    {
        $user_id_hash = hash('sha256', $this->user_id);
        $storagePath .= '/' . $user_id_hash . '/chapter_videos';

        // check if storage path is valid
        if (!Storage::disk(getDisk())->exists($storagePath)) {
            Storage::disk(getDisk())->makeDirectory($storagePath);
        }

        if ($video instanceof UploadedFile) {
            tap($this->chapter_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'chapter_video' => $video->storePublicly(
                        $storagePath,
                        ['disk' => getDisk()]
                    ),
                ])->save();

                if ($previous) {
                    Storage::disk(getDisk())->delete($previous);
                }
            });
        } else if ($video instanceof File) {
            tap($this->chapter_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'chapter_video' => Storage::disk(getDisk())->put($storagePath, $video),
                ])->save();
                if ($previous) {
                    Storage::disk(getDisk())->delete($previous);
                }
            });
        } else if (is_string($video)) {
            // If $video is string (path del archivo), move it using streams to avoid memory problems
            tap($this->chapter_video, function ($previous) use ($video, $storagePath) {
                $originalExtension = pathinfo($video, PATHINFO_EXTENSION);
                $filename = uniqid('chapter_') . '.' . $originalExtension;
                $destinationPath = $storagePath . '/' . $filename;
                
                $disk = Storage::disk(getDisk());
                $chunkSize = 2 * 1024 * 1024; // 2MB chunks
                
                // Open the source file for reading
                $sourceHandle = fopen($video, 'rb');
                if (!$sourceHandle) {
                    throw new \Exception('Could not open the source file: ' . $video);
                }
                
                // Create a temporary stream to write the chunks
                $tempStream = fopen('php://temp', 'w+b');
                if (!$tempStream) {
                    fclose($sourceHandle);
                    throw new \Exception('Could not create the temporary stream');
                }
                
                try {
                    // Read and write in chunks to avoid loading the entire file into memory
                    while (!feof($sourceHandle)) {
                        $chunk = fread($sourceHandle, $chunkSize);
                        if ($chunk === false) {
                            throw new \Exception('Error reading the chunk of the file');
                        }
                        fwrite($tempStream, $chunk);
                    }
                    
                    // Rewind the temporary stream to be able to read it from the beginning
                    rewind($tempStream);
                    
                    // Save the stream in the destination disk
                    $disk->put($destinationPath, $tempStream);
                    
                    $this->forceFill([
                        'chapter_video' => $destinationPath,
                    ])->save();
                    
                    if ($previous) {
                        Storage::disk(getDisk())->delete($previous);
                    }
                    
                } finally {
                    // Close all handles
                    fclose($sourceHandle);
                    fclose($tempStream);
                    
                    // Delete the temporary file after moving it
                    if (file_exists($video)) {
                        unlink($video);
                    }
                }
            });
        }
    }

    /**
     * Get the default chapter video URL if no chapter video has been uploaded.
     * @return string
     */
    protected function defaultChapterVideo()
    {
        return '';
    }

}
