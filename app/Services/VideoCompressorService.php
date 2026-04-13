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

        $safeVideoFilter = escapeshellarg($config['vf']);

        $ffmpegPath = config('app.ffmpeg_path', '/usr/bin/ffmpeg');

        // Construir comando FFmpeg con preset ultrafast para velocidad
        $command = "{$ffmpegPath} -hide_banner -loglevel error "
            . "-i {$safeInputPath} "
            // Usar el filtro escapado
            . "-vf {$safeVideoFilter} " 
            . "-c:v libx264 "
            . "-crf {$config['crf']} "
            . "-preset ultrafast "
            . "-tune fastdecode "
            . "-c:a aac "
            . "-b:a 128k "
            . "-movflags +faststart "
            . "-y {$safeOutputPath} 2>&1";

        // Ejecutar FFmpeg
        exec($command, $output, $returnCode);

        // Log para depuración
        $this->logCommand($command, $output, $returnCode);

        if ($returnCode === 0) {
            return $outputPath;
        }

        // Si hay error, lanzar excepción con mensaje detallado
        throw new \Exception("FFmpeg error (Code {$returnCode}):\nComando: {$command}\nSalida: " . implode("\n", $output));
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

    /**
     * Convierte un video a formato MP4 de la manera más rápida posible.
     * Primero intenta stream-copy (instantáneo), si falla, re-codifica con ultrafast.
     *
     * @param string $inputPath Ruta del video de entrada
     * @param string $outputPath Ruta de salida del video en MP4
     * @return string Ruta de salida si la conversión fue exitosa
     * @throws \Exception Si FFmpeg devuelve un error
     */
    public function justConvertToMp4($inputPath, $outputPath)
    {
        // Verificar que el archivo de entrada exista
        if (!file_exists($inputPath)) {
            throw new \Exception("El archivo de entrada no existe: {$inputPath}");
        }

        $ffmpegPath = config('app.ffmpeg_path', '/usr/bin/ffmpeg');

        // Escapar rutas (protección contra inyección de comandos)
        $safeInputPath = escapeshellarg($inputPath);
        $safeOutputPath = escapeshellarg($outputPath);

        // INTENTO 1: Stream copy (instantáneo, sin re-codificación)
        // Esto funciona para .mov con codec H.264/AAC — sin pérdida de calidad y es ~100x más rápido
        $commandCopy = "{$ffmpegPath} -hide_banner -loglevel error "
            . "-i {$safeInputPath} "
            . "-c:v copy "
            . "-c:a copy "
            . "-movflags +faststart "
            . "-y {$safeOutputPath} 2>&1";

        exec($commandCopy, $outputCopy, $returnCodeCopy);
        $this->logCommand($commandCopy, $outputCopy, $returnCodeCopy);

        if ($returnCodeCopy === 0 && file_exists($outputPath) && filesize($outputPath) > 1000) {
            return $outputPath;
        }

        // INTENTO 2: Re-codificar con ultrafast preset si stream copy falló
        $command = "{$ffmpegPath} -hide_banner -loglevel error "
            . "-i {$safeInputPath} "
            . "-c:v libx264 "
            . "-preset ultrafast "
            . "-crf 23 "
            . "-c:a aac "
            . "-b:a 128k "
            . "-movflags +faststart "
            . "-y {$safeOutputPath} 2>&1";

        exec($command, $output, $returnCode);
        $this->logCommand($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 1000) {
            return $outputPath;
        }

        // Si hay error, lanzar excepción con mensaje detallado
        throw new \Exception("FFmpeg error (Code {$returnCode}):\nComando: {$command}\nSalida: " . implode("\n", $output));
    }

    /**
     * Log FFmpeg commands for debugging.
     */
    private function logCommand($command, $output, $returnCode)
    {
        $logPath = storage_path('logs/ffmpeg.log');
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] Code: {$returnCode}\nCommand: {$command}\nOutput: " . implode("\n", $output) . "\n---\n";
        file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
    }
}