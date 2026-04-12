<?php

namespace App\Traits\Serie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\File;
use App\Services\VideoCompressorService;

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
    
            return \App\Services\S3UrlService::temporaryUrl($this->chapter_video) 
                ?? $this->defaultChapterVideo();
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
        ensureStorageDirectory($storagePath);

        $compressor = new VideoCompressorService();

        if ($video instanceof UploadedFile || $video instanceof File) {
            // el video no es chunk, se guarda directamente usando el disco con getDisk()
            tap($this->chapter_video, function ($previous) use ($video, $storagePath, $compressor) {

                $sourcePath = $video->getRealPath();
                $originalExtension = strtolower($video->getClientOriginalExtension());

                // Si el video ya es mp4, guardarlo directamente
                if ($originalExtension === 'mp4') {
                    $storedPath = Storage::disk(getDisk())->putFile(
                        $storagePath,
                        $video
                    );

                    $this->forceFill([
                        'chapter_video' => $storedPath,
                    ])->save();

                    if ($previous) {
                        Storage::disk(getDisk())->delete($previous);
                    }
                } else {
                    // Si no es mp4, convertirlo
                    $convertedPath = tempnam(sys_get_temp_dir(), 'conv_chapter_') . '.mp4';

                    try {
                        $compressor->justConvertToMp4($sourcePath, $convertedPath);
                        $storedPath = Storage::disk(getDisk())->putFile(
                            $storagePath,
                            new File($convertedPath),
                        );

                        $this->forceFill([
                            'chapter_video' => $storedPath,
                        ])->save();

                        if ($previous) {
                            Storage::disk(getDisk())->delete($previous);
                        }
                    } finally {
                        if (file_exists($convertedPath)) {
                            unlink($convertedPath);
                        }
                    }
                }
            });
        } else if (is_string($video)) {
            // Si $video es string (path del archivo), moverlo usando streams para evitar problemas de memoria
            tap($this->chapter_video, function ($previous) use ($video, $storagePath, $compressor) {

                $sourcePath = $video;
                $originalExtension = strtolower(pathinfo($video, PATHINFO_EXTENSION));

                // Si el video ya es mp4, guardarlo directamente
                if ($originalExtension === 'mp4') {
                    $filename = uniqid('chapter_') . '.mp4';
                    $destinationPath = $storagePath . '/' . $filename;

                    $disk = Storage::disk(getDisk());
                    $sourceHandle = null;

                    try {
                        $sourceHandle = fopen($sourcePath, 'rb');
                        if (!$sourceHandle) {
                            throw new \Exception('No se pudo abrir el archivo fuente: ' . $sourcePath);
                        }

                        $disk->put($destinationPath, $sourceHandle);

                        $this->forceFill([
                            'chapter_video' => $destinationPath,
                        ])->save();

                        if ($previous) {
                            Storage::disk(getDisk())->delete($previous);
                        }
                    } finally {
                        if (isset($sourceHandle) && is_resource($sourceHandle)) {
                            fclose($sourceHandle);
                        }
                        if (file_exists($sourcePath)) {
                            unlink($sourcePath);
                        }
                    }
                } else {
                    // Si no es mp4, convertirlo
                    $filename = uniqid('chapter_') . '.mp4';
                    $destinationPath = $storagePath . '/' . $filename;
                    $convertedPath = tempnam(sys_get_temp_dir(), 'conv_chapter_') . '.mp4';

                    $disk = Storage::disk(getDisk());
                    $convertedHandle = null;

                    try {
                        $compressor->justConvertToMp4($sourcePath, $convertedPath);

                        $convertedHandle = fopen($convertedPath, 'rb');
                        if (!$convertedHandle) {
                            throw new \Exception('No se pudo abrir el archivo comprimido: ' . $convertedPath);
                        }

                        $disk->put($destinationPath, $convertedHandle);

                        $this->forceFill([
                            'chapter_video' => $destinationPath,
                        ])->save();

                        if ($previous) {
                            Storage::disk(getDisk())->delete($previous);
                        }
                    } finally {
                        if (isset($convertedHandle) && is_resource($convertedHandle)) {
                            fclose($convertedHandle);
                        }
                        if (file_exists($convertedPath)) {
                            unlink($convertedPath);
                        }
                        if (file_exists($sourcePath)) {
                            unlink($sourcePath);
                        }
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
