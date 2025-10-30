<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        // Crear una copia temporal optimizada
        $optimizedImage = Image::make($file);

        // Optimizar la imagen
        foreach (self::$qualities as $quality) {
            if ($optimizedImage->filesize() > $quality['size']) {
                $optimizedImage->encode('webp', $quality['quality']);
                break;
            }
        }

        if ($optimizedImage->mime() != 'image/webp') {
            $optimizedImage->encode('webp', $quality);
        }

        // Crear un nuevo UploadedFile con la imagen optimizada
        $tempPath = tempnam(sys_get_temp_dir(), 'optimized_') . '.webp';
        $optimizedImage->save($tempPath);

        return new UploadedFile(
            $tempPath,
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            null,
            true
        );
    }

    public static function convertToWebP(UploadedFile $file, $quality = 90)
    {
        $image = Image::make($file->getRealPath());

        $image->encode('webp', $quality);


        $tempPath = tempnam(sys_get_temp_dir(), 'webp_') . '.webp';
        $image->save($tempPath);

        return new UploadedFile(
            $tempPath,
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp',
            'image/webp',
            null,
            true
        );
    }
}
