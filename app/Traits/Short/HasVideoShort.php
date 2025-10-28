<?php

namespace App\Traits\Short;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\File;

trait HasVideoShort
{

    /**
     * Update the trailer video's profile video.
     * 
     * @return void
     */
    public function deleteVideoShort(): void
    {
        if (is_null($this->short_video)) {
            return;
        }

        Storage::disk($this->VideoShortDisk())->delete($this->short_video);

        $this->forceFill([
            'short_video' => '',
        ])->save();
    }

    /**
     * Get the URL to the trailer video's profile video.
     */
    public function shortVideoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->short_video
                ? Storage::disk($this->VideoShortDisk())->url($this->short_video)
                : $this->defaultShortVideo();
        });
    }

    /**
     * Update the trailer video's profile video.
     * 
     * @param  $video
     * @param  string  $storagePath
     */
    public function updateVideoShort($video, $storagePath = 'shorts/short_videos')
    {
        $storagePath = 'user_' . $this->user_id . '/' . 'shorts/short_videos';
        if ($video instanceof UploadedFile) {
            tap($this->short_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'short_video' => $video->storePublicly(
                        $storagePath,
                        ['disk' => $this->VideoShortDisk()]
                    ),
                ])->save();

                if ($previous) {
                    Storage::disk($this->VideoShortDisk())->delete($previous);
                }
            });
        } else if ($video instanceof File) {
            tap($this->short_video, function ($previous) use ($video, $storagePath) {
                $this->forceFill([
                    'short_video' => Storage::disk($this->VideoShortDisk())->put($storagePath, $video),
                ])->save();
                if ($previous) {
                    Storage::disk($this->VideoShortDisk())->delete($previous);
                }
            });
        } else if (is_string($video)) {
            // Si $video es string (path del archivo), moverlo usando streams para evitar problemas de memoria
            tap($this->short_video, function ($previous) use ($video, $storagePath) {
                $originalExtension = pathinfo($video, PATHINFO_EXTENSION);
                $filename = uniqid('short_') . '.' . $originalExtension;
                $destinationPath = $storagePath . '/' . $filename;
                
                $disk = Storage::disk($this->VideoShortDisk());
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
                        'short_video' => $destinationPath,
                    ])->save();
                    
                    if ($previous) {
                        Storage::disk($this->VideoShortDisk())->delete($previous);
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
    protected function defaultShortVideo()
    {
        return '';
    }

    /**
     * Get the disk that trailer videos should be stored on.
     *
     * @return string
     */
    protected function VideoShortDisk()
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : 'public';
    }
}
