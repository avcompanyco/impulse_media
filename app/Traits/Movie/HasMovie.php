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

        Storage::disk($this->MovievideoDisk())->delete($this->movie_video);

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
    
            $disk = Storage::disk($this->MovievideoDisk());
    
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
    public function updateMovieVideo($video, $storagePath = 'movies/movie_videos')
    {
        $storagePath = 'user_' . $this->user_id . '/' . 'movies/movie_videos';

        if ($video instanceof UploadedFile) {
            tap($this->movie_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'movie_video' => $video->storePublicly(
                        $storagePath,
                        ['disk' => $this->MovievideoDisk()]
                    ),
                ])->save();

                if ($previous) {
                    Storage::disk($this->MovievideoDisk())->delete($previous);
                }
            });
        } else if ($video instanceof File) {
            tap($this->movie_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'movie_video' => Storage::disk($this->MovievideoDisk())->put($storagePath, $video),
                ])->save();
                if ($previous) {
                    Storage::disk($this->MovievideoDisk())->delete($previous);
                }
            });
        } else if (is_string($video)) {
            // Si $video es string (path del archivo), moverlo usando streams para evitar problemas de memoria
            tap($this->movie_video, function ($previous) use ($video, $storagePath) {
                $originalExtension = pathinfo($video, PATHINFO_EXTENSION);
                $filename = uniqid('movie_') . '.' . $originalExtension;
                $destinationPath = $storagePath . '/' . $filename;

                $disk = Storage::disk($this->MovievideoDisk());
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
                        'movie_video' => $destinationPath,
                    ])->save();

                    if ($previous) {
                        Storage::disk($this->MovievideoDisk())->delete($previous);
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
     * Get the default movie video URL if no profile video has been uploaded.
     * @return string
     */
    protected function defaultMovieVideo()
    {
        return '';
    }

    /**
     * Get the disk that movie videos should be stored on.
     *
     * @return string
     */
    protected function MovievideoDisk()
    {
        return isset($_ENV['FILESYSTEM_DISK']) ? 's3' : 'public';
    }
}
