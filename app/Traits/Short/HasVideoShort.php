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

            $disk = Storage::disk(getDisk());

            try {
                return $disk->temporaryUrl($this->short_video, now()->addHour());
            } catch (\Throwable $e) {
                return $disk->url($this->short_video);
            }
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

        $compressor = new VideoCompressorService();

        // CASO 1 Y 2: $video es UploadedFile O File
        // (Maneja subidas directas, no-chunked)
        if ($video instanceof UploadedFile || $video instanceof File) {
            
            tap($this->short_video, function ($previous) use ($video, $storagePath, $compressor) {
                
                $sourcePath = $video->getRealPath();
                // $originalExtension = $video->guessExtension() ?: 'mp4'; 
                $originalExtension = 'mp4';

                $compressedPath = tempnam(sys_get_temp_dir(), 'comp_short_') . '.' . $originalExtension;

                try {
                    // Comprimir
                    $compressor->compressVideo($sourcePath, $compressedPath);
                    
                    // Guardar el video *comprimido*
                    $storedPath = Storage::disk(getDisk())->putFile(
                        $storagePath,
                        new File($compressedPath), // Usamos new File() para el archivo comprimido
                    );

                    // Actualizar DB
                    $this->forceFill([
                        'short_video' => $storedPath,
                    ])->save();

                    // Eliminar video anterior
                    if ($previous) {
                        Storage::disk(getDisk())->delete($previous);
                    }
                } finally {
                    // Limpiar el temporal comprimido
                    if (file_exists($compressedPath)) {
                        unlink($compressedPath);
                    }
                }
            });
        
        // CASO 3: $video es un string (path)
        // (Maneja la subida por chunks, después de que se unen)
        } else if (is_string($video)) {
            
            tap($this->short_video, function ($previous) use ($video, $storagePath, $compressor) {
                
                $sourcePath = $video; // $video es el path al archivo unido
                // $originalExtension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                $originalExtension = 'mp4';
                
                $filename = uniqid('short_') . '.' . $originalExtension;
                $destinationPath = $storagePath . '/' . $filename;
                $compressedPath = tempnam(sys_get_temp_dir(), 'comp_short_') . '.' . $originalExtension;

                $disk = Storage::disk(getDisk());
                $compressedHandle = null;

                try {
                    // 1. Comprimir
                    $compressor->compressVideo($sourcePath, $compressedPath);

                    // 2. Abrir stream del *comprimido*
                    $compressedHandle = fopen($compressedPath, 'rb');
                    if (!$compressedHandle) {
                        throw new \Exception('No se pudo abrir el archivo comprimido: ' . $compressedPath);
                    }

                    // 3. Guardar stream en disco
                    $disk->put($destinationPath, $compressedHandle); 

                    // 4. Actualizar DB
                    $this->forceFill([
                        'short_video' => $destinationPath,
                    ])->save();

                    // 5. Eliminar video anterior
                    if ($previous) {
                        Storage::disk(getDisk())->delete($previous);
                    }
                } finally {
                    // 6. Limpieza de TODOS los temporales
                    if (isset($compressedHandle) && $compressedHandle) {
                        fclose($compressedHandle);
                    }
                    if (file_exists($compressedPath)) {
                        unlink($compressedPath); // Borra el comprimido
                    }
                    if (file_exists($sourcePath)) {
                        unlink($sourcePath); // Borra el original (unido de chunks)
                    }
                }
            });
        
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