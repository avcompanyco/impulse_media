<?php

namespace App\Traits\Short;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\File; // <-- Importamos Illuminate\Http\File
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\VideoCompressorService;

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

        // CASO 1: UploadedFile or File (direct upload, non-chunked)
        if ($video instanceof UploadedFile || $video instanceof File) {
            
            // Upload directly to S3 — no FFmpeg processing for reliability
            $storedPath = $disk->putFile($storagePath, $video);

            if ($storedPath) {
                $this->forceFill(['short_video' => $storedPath])->save();

                if ($previousVideo) {
                    $disk->delete($previousVideo);
                }
            }

        // CASO 2: String path (chunked upload merged file)
        } else if (is_string($video)) {
            
            $sourcePath = $video;
            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) ?: 'mp4';
            $filename = uniqid('short_') . '.' . $extension;
            $destinationPath = $storagePath . '/' . $filename;

            $uploaded = false;

            try {
                // Upload the raw merged file directly to S3
                $handle = fopen($sourcePath, 'rb');
                if ($handle) {
                    $disk->put($destinationPath, $handle);
                    if (is_resource($handle)) {
                        fclose($handle);
                    }
                    $uploaded = true;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Short upload failed: ' . $e->getMessage());
            }

            if ($uploaded) {
                $this->forceFill(['short_video' => $destinationPath])->save();

                if ($previousVideo) {
                    $disk->delete($previousVideo);
                }
            }

            // Clean up the temp chunk file
            if (file_exists($sourcePath)) {
                unlink($sourcePath);
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