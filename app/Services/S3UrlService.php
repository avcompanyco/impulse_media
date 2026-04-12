<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class S3UrlService
{
    /**
     * Get a cached temporary URL for an S3 object.
     * Caches the signed URL for 12 hours (URL expires in 24 hours).
     *
     * @param string $path The S3 file path
     * @param string $disk The disk name
     * @return string|null
     */
    public static function temporaryUrl(string $path, string $disk = null): ?string
    {
        if (!$path) {
            return null;
        }

        $disk = $disk ?: getDisk();
        $cacheKey = 's3_url_' . md5($path . $disk);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($path, $disk) {
            try {
                return Storage::disk($disk)->temporaryUrl($path, now()->addDay());
            } catch (\Throwable $e) {
                return Storage::disk($disk)->url($path);
            }
        });
    }

    /**
     * Forget the cached URL for a path (call after updating the file).
     *
     * @param string $path
     * @param string $disk
     */
    public static function forgetUrl(string $path, string $disk = null): void
    {
        $disk = $disk ?: getDisk();
        $cacheKey = 's3_url_' . md5($path . $disk);
        Cache::forget($cacheKey);
    }
}
