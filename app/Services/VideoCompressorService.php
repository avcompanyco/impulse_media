<?php

namespace App\Services;

class VideoCompressorService
{
    /**
     * Comprime un video a la calidad especificada usando FFmpeg.
     *
     * @param string $inputPath Ruta del video de entrada
     * @param string $outputPath Ruta de salida del video comprimido
     * @param string $quality ('480p'|'720p'|'1080p')
     * @return string Ruta de salida si la compresión fue exitosa
     * @throws \Exception Si FFmpeg devuelve un error
     */
    public function compressVideo($inputPath, $outputPath, $quality = '720p')
    {
        // Verificar que el archivo de entrada exista
        if (!file_exists($inputPath)) {
            throw new \Exception("El archivo de entrada no existe: {$inputPath}");
        }

        $config = $this->getQualityConfig($quality);

        // Escapar rutas (protección contra inyección de comandos)
        $safeInputPath = escapeshellarg($inputPath);
        $safeOutputPath = escapeshellarg($outputPath);

        // Filtro de video (sin comillas dobles internas)
        $videoFilter = $config['vf'];

        // Construir comando FFmpeg
        $command = "ffmpeg -hide_banner -loglevel error "
            . "-i {$safeInputPath} "
            . "-vf {$videoFilter} "
            . "-c:v libx264 "
            . "-crf {$config['crf']} "
            . "-preset fast "
            . "-c:a aac "
            . "-b:a 128k "
            . "-movflags +faststart "
            . "-y {$safeOutputPath} 2>&1";

        // Ejecutar FFmpeg
        exec($command, $output, $returnCode);

        // Depuración opcional:
        // file_put_contents('ffmpeg.log', implode("\n", $output));

        if ($returnCode === 0) {
            return $outputPath;
        }

        // Si hay error, lanzar excepción con mensaje detallado
        throw new \Exception("FFmpeg error (Code {$returnCode}):\n" . implode("\n", $output));
    }

    /**
     * Devuelve la configuración de FFmpeg según la calidad deseada.
     *
     * @param string $quality
     * @return array
     */
    private function getQualityConfig($quality)
    {
        $configs = [
            '480p' => [
                'vf' => "scale=-2:480,crop=trunc(iw/2)*2:trunc(ih/2)*2",
                'crf' => 25
            ],
            '720p' => [
                'vf' => "scale=-2:720,crop=trunc(iw/2)*2:trunc(ih/2)*2",
                'crf' => 23
            ],
            '1080p' => [
                'vf' => "scale=-2:1080,crop=trunc(iw/2)*2:trunc(ih/2)*2",
                'crf' => 21
            ],
        ];

        return $configs[$quality] ?? $configs['720p'];
    }
}
