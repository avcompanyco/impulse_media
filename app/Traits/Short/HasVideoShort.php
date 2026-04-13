<?php

namespace App\Traits\Short;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasVideoShort
{

    /**
     * Borra el video short actual.
     *
     * @return void
     */
    public function deleteVideoShort(): void
    {
        if (is_null($this->short_video)) {
            return;
        }

        Storage::disk(getDisk())->delete($this->short_video);

        $this->forceFill([
            'short_video' => '',
        ])->save();
    }

    /**
     * Obtiene la URL del video short.
     */
    public function shortVideoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->short_video) {
                return $this->defaultShortVideo();
            }

            return \App\Services\S3UrlService::temporaryUrl($this->short_video) 
                ?? $this->defaultShortVideo();
        });
    }

    /**
     * Actualiza y comprime el video short.
     *
     * @param  \Illuminate\Http\UploadedFile|\Illuminate\Http\File|string  $video
     * @param  string  $storagePath
     */
    public function updateVideoShort($video, $storagePath = 'shorts')
    {
        $user_id_hash = hash('sha256', $this->user_id);
        $storagePath .= '/' . $user_id_hash;

        ensureStorageDirectory($storagePath);

        $disk = Storage::disk(getDisk());
        $previousVideo = $this->short_video;

        Log::info('updateVideoShort called', [
            'type' => is_string($video) ? 'string' : get_class($video),
            'short_id' => $this->id,
            'storagePath' => $storagePath,
        ]);

        // CASO 1: UploadedFile or File (direct upload, non-chunked)
        if ($video instanceof UploadedFile || $video instanceof File) {
            
            // Upload directly to S3 — putFile auto-detects MIME type
            $storedPath = $disk->putFile($storagePath, $video);

            Log::info('Short direct upload', ['storedPath' => $storedPath]);

            if ($storedPath) {
                $this->forceFill(['short_video' => $storedPath])->save();

                if ($previousVideo) {
                    $disk->delete($previousVideo);
                }
            }

        // CASO 2: String path (chunked upload merged file)
        } else if (is_string($video)) {
            
            $sourcePath = $video;
            
            Log::info('Short chunk upload', [
                'sourcePath' => $sourcePath,
                'exists' => file_exists($sourcePath),
                'size' => file_exists($sourcePath) ? filesize($sourcePath) : 0,
            ]);

            if (!file_exists($sourcePath) || filesize($sourcePath) < 100) {
                Log::error('Short chunk file missing or too small', ['path' => $sourcePath]);
                return;
            }

            $uploaded = false;

            try {
                // Use putFile with File object — auto-detects MIME type from file
                $fileObj = new File($sourcePath);
                
                $storedPath = $disk->putFile($storagePath, $fileObj);

                Log::info('Short chunk stored to S3', ['storedPath' => $storedPath]);

                if ($storedPath) {
                    $uploaded = true;
                }
            } catch (\Throwable $e) {
                Log::error('Short chunk upload failed: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            if ($uploaded) {
                $this->forceFill(['short_video' => $storedPath])->save();

                if ($previousVideo) {
                    $disk->delete($previousVideo);
                }
            }

            // Clean up the temp chunk file
            if (file_exists($sourcePath)) {
                @unlink($sourcePath);
            }

        } else {
             throw new \InvalidArgumentException('El tipo de video proporcionado no es soportado. Debe ser UploadedFile, File o string (path).');
        }
    }

    /**
     * Get the default trailer video URL if no profile video has been uploaded.
     * @return string
     */
    protected function defaultShortVideo()
    {
        return '';
    }
}