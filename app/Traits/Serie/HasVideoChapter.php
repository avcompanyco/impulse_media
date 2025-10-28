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

        Storage::disk($this->ChapterVideoDisk())->delete($this->chapter_video);

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
            return $this->chapter_video
                ? Storage::disk($this->ChapterVideoDisk())->url($this->chapter_video)
                : $this->defaultChapterVideo();
        });
    }

    /**
     * Update the chapter video's profile video.
     * 
     * @param  $video
     * @param  string  $storagePath
     */
    public function updateChapterVideo($video, $storagePath = 'series/chapter_videos')
    {
        // se usa $this->user_id porque el trait lo usa un modelo que tiene un user_id
        $storagePath = 'user_' . $this->user_id . '/' . 'series/chapter_videos';
        if ($video instanceof UploadedFile) {
            tap($this->chapter_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'chapter_video' => $video->storePublicly(
                        $storagePath,
                        ['disk' => $this->ChapterVideoDisk()]
                    ),
                ])->save();

                if ($previous) {
                    Storage::disk($this->ChapterVideoDisk())->delete($previous);
                }
            });
        } else if ($video instanceof File) {
            tap($this->chapter_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'chapter_video' => Storage::disk($this->ChapterVideoDisk())->put($storagePath, $video),
                ])->save();
                if ($previous) {
                    Storage::disk($this->ChapterVideoDisk())->delete($previous);
                }
            });
        } else if (is_string($video)) {
            // If $video is string (path del archivo), move it using streams to avoid memory problems
            tap($this->chapter_video, function ($previous) use ($video, $storagePath) {
                $originalExtension = pathinfo($video, PATHINFO_EXTENSION);
                $filename = uniqid('chapter_') . '.' . $originalExtension;
                $destinationPath = $storagePath . '/' . $filename;
                
                $disk = Storage::disk($this->ChapterVideoDisk());
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
                        Storage::disk($this->ChapterVideoDisk())->delete($previous);
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
        $name = trim(collect(explode(' ', $this->title))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that chapter videos should be stored on.
     *
     * @return string
     */
    protected function ChapterVideoDisk()
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : 'public';
    }
}
