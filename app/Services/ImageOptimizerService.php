<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Http\UploadedFile;

class ImageOptimizerService
{
    public static $qualities = [
        [
            'size' => 5000000,
            'quality' => 35
        ],
        [
            'size' => 4000000,
            'quality' => 40
        ],
        [
            'size' => 3000000,
            'quality' => 45
        ],
        [
            'size' => 2000000,
            'quality' => 50
        ],
        [
            'size' => 1000000,
            'quality' => 55
        ],
        [
            'size' => 500000,
            'quality' => 60
        ],
        [
            'size' => 200000,
            'quality' => 70
        ]
    ];

    public static function optimizeUploadedFile(UploadedFile $file, $quality = 75)
    {
        // Leer la imagen usando el facade de Laravel
        $optimizedImage = Image::read($file->getRealPath());

        // Obtener el tamaño del archivo original
        $fileSize = $file->getSize();

        // Determinar la calidad basada en el tamaño del archivo
        $selectedQuality = $quality;
        foreach (self::$qualities as $qualitySetting) {
            if ($fileSize > $qualitySetting['size']) {
                $selectedQuality = $qualitySetting['quality'];
                break;
            }
        }

        // Convertir a WebP con la calidad seleccionada
        $encodedImage = $optimizedImage->encode(new WebpEncoder($selectedQuality));

        // Crear un nuevo UploadedFile con la imagen optimizada
        $tempPath = tempnam(sys_get_temp_dir(), 'optimized_') . '.webp';
        file_put_contents($tempPath, $encodedImage->toString());

        return new UploadedFile(
            $tempPath,
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp',
            'image/webp',
            null,
            true
        );
    }

    public static function convertToWebP(UploadedFile $file, $quality = 90)
    {
        // Leer la imagen
        $image = Image::read($file->getRealPath());

        // Codificar a WebP
        $encodedImage = $image->encode(new WebpEncoder($quality));

        // Guardar en archivo temporal
        $tempPath = tempnam(sys_get_temp_dir(), 'webp_') . '.webp';
        file_put_contents($tempPath, $encodedImage->toString());

        return new UploadedFile(
            $tempPath,
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp',
            'image/webp',
            null,
            true
        );
    }
}