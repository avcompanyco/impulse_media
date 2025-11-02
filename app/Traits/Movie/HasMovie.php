<?php

namespace App\Traits\Movie;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\File;


trait HasMovie
{

    /**
     * Update the movie video's profile video.
     * 
     * @return void
     */
    public function deleteMovie(): void
    {
        if (is_null($this->movie_video)) {
            return;
        }

        Storage::disk(getVideoDisk())->delete($this->movie_video);

        $this->forceFill([
            'movie_video' => '',
        ])->save();
    }

    /**
     * Get the URL to the movie video's profile video.
     */
    public function movieVideoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->movie_video) {
                return $this->defaultMovieVideo();
            }
    
            $disk = Storage::disk(getVideoDisk());
    
            try {
                // URL temporal válida por 1 hora
                return $disk->temporaryUrl($this->movie_video, now()->addHour());
            } catch (\Throwable $e) {
                // fallback a URL normal si el disco no soporta temporaryUrl
                return $disk->url($this->movie_video);
            }
        });
    }

    /**
     * Update the movie video's profile video.
     * 
     * @param   $video
     * @param  string  $storagePath
     */
    public function updateMovieVideo($video, $storagePath = 'movies')
    {
        $user_id_hash = hash('sha256', $this->user_id);
        $storagePath .= '/' . $user_id_hash . '/movie_videos';
        // check if storage path is valid
        if (!Storage::disk(getDisk())->exists($storagePath)) {
            Storage::disk(getDisk())->makeDirectory($storagePath);
        }

        if ($video instanceof UploadedFile) {
            // el video no es chunk, se guarda directamente usando el disco con getDisk()
            tap($this->movie_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'movie_video' => $video->storePublicly(
                        $storagePath,
                        ['disk' => getDisk()]
                    ),
                ])->save();

                if ($previous) {
                    Storage::disk(getDisk())->delete($previous);
                }
            });
        } else if ($video instanceof File) {
            // el video no es chunk, se guarda directamente usando el disco con getDisk()
            tap($this->movie_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'movie_video' => Storage::disk(getDisk())->put($storagePath, $video),
                ])->save();
                if ($previous) {
                    Storage::disk(getDisk())->delete($previous);
                }
            });
        } else if (is_string($video)) {
            // Si $video es string (path del archivo), moverlo usando streams para evitar problemas de memoria
            tap($this->movie_video, function ($previous) use ($video, $storagePath) {
                $originalExtension = pathinfo($video, PATHINFO_EXTENSION);
                $filename = uniqid('movie_') . '.' . $originalExtension;
                $destinationPath = $storagePath . '/' . $filename;

                $disk = Storage::disk(getDisk());
                $chunkSize = 2 * 1024 * 1024; // 2MB chunks

                $tempStream = null;
                $sourceHandle = null;
                
                try {
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
                        'movie_video' => $destinationPath,
                    ])->save();

                    if ($previous) {
                        Storage::disk(getDisk())->delete($previous);
                    }
                } finally {
                    // Cerrar todos los handles
                    if (isset($sourceHandle) && $sourceHandle) {
                        fclose($sourceHandle);
                    }
                    if (isset($tempStream) && $tempStream) {
                        fclose($tempStream);
                    }

                    // Eliminar el archivo temporal después de moverlo
                    if (file_exists($video)) {
                        unlink($video);
                    }
                }
            });
        }
    }

    /**
     * Get the default movie video URL if no profile video has been uploaded.
     * @return string
     */
    protected function defaultMovieVideo()
    {
        return '';
    }
    
}
