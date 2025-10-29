<?php

namespace App\Traits\Movie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\File;

trait HasTrailerMovie
{

    /**
     * Update the trailer video's profile video.
     * 
     * @return void
     */
    public function deleteTrailerMovie(): void
    {
        if (is_null($this->trailer_video)) {
            return;
        }

        Storage::disk($this->TrailervideoDisk())->delete($this->trailer_video);

        $this->forceFill([
            'trailer_video' => '',
        ])->save();
    }

    /**
     * Get the URL to the trailer video's profile video.
     */
    public function trailerVideoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->trailer_video) {
                return $this->defaultTrailerVideo();
            }
    
            $disk = Storage::disk($this->TrailervideoDisk());
    
            try {
                // URL temporal válida por 1 hora
                return $disk->temporaryUrl($this->trailer_video, now()->addHour());
            } catch (\Throwable $e) {
                // fallback a URL normal si el disco no soporta temporaryUrl
                return $disk->url($this->trailer_video);
            }
        });
    }

    /**
     * Update the trailer video's profile video.
     * 
     * @param  $video
     * @param  string  $storagePath
     */
    public function updateTrailerVideo($video, $storagePath = 'movies/trailer_videos')
    {
        $storagePath = 'user_' . $this->user_id . '/' . 'movies/trailer_videos';
        if ($video instanceof UploadedFile) {
            tap($this->trailer_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'trailer_video' => $video->storePublicly(
                        $storagePath,
                        ['disk' => $this->TrailervideoDisk()]
                    ),
                ])->save();

                if ($previous) {
                    Storage::disk($this->TrailervideoDisk())->delete($previous);
                }
            });
        } else if ($video instanceof File) {
            tap($this->trailer_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'trailer_video' => Storage::disk($this->TrailervideoDisk())->put($storagePath, $video),
                ])->save();
                if ($previous) {
                    Storage::disk($this->TrailervideoDisk())->delete($previous);
                }
            });
        } else if (is_string($video)) {
            // Si $video es string (path del archivo), moverlo usando streams para evitar problemas de memoria
            tap($this->trailer_video, function ($previous) use ($video, $storagePath) {
                $originalExtension = pathinfo($video, PATHINFO_EXTENSION);
                $filename = uniqid('trailer_') . '.' . $originalExtension;
                $destinationPath = $storagePath . '/' . $filename;
                
                $disk = Storage::disk($this->TrailervideoDisk());
                $chunkSize = 2 * 1024 * 1024; // 2MB chunks
                
                // Abrir el archivo fuente para lectura
                $sourceHandle = fopen($video, 'rb');
                if (!$sourceHandle) {
                    throw new \Exception('No se pudo abrir el archivo fuente: ' . $video);
                }
                
                // Crear un stream temporal para escribir los chunks
                $tempStream = fopen('php://temp', 'w+b');
                if (!$tempStream) {
                    fclose($sourceHandle);
                    throw new \Exception('No se pudo crear el stream temporal');
                }
                
                try {
                    // Leer y escribir en chunks para evitar cargar todo el archivo en memoria
                    while (!feof($sourceHandle)) {
                        $chunk = fread($sourceHandle, $chunkSize);
                        if ($chunk === false) {
                            throw new \Exception('Error al leer el chunk del archivo');
                        }
                        fwrite($tempStream, $chunk);
                    }
                    
                    // Rewind el stream temporal para poder leerlo desde el inicio
                    rewind($tempStream);
                    
                    // Guardar el stream en el disco de destino
                    $disk->put($destinationPath, $tempStream);
                    
                    $this->forceFill([
                        'trailer_video' => $destinationPath,
                    ])->save();
                    
                    if ($previous) {
                        Storage::disk($this->TrailervideoDisk())->delete($previous);
                    }
                    
                } finally {
                    // Cerrar todos los handles
                    fclose($sourceHandle);
                    fclose($tempStream);
                    
                    // Eliminar el archivo temporal después de moverlo
                    if (file_exists($video)) {
                        unlink($video);
                    }
                }
            });
        }
    }

    /**
     * Get the default trailer video URL if no profile video has been uploaded.
     * @return string
     */
    protected function defaultTrailerVideo()
    {
        return '';
    }

    /**
     * Get the disk that trailer videos should be stored on.
     *
     * @return string
     */
    protected function TrailervideoDisk()
    {
        return isset($_ENV['FILESYSTEM_DISK']) ? 's3' : 'public';
    }
}
