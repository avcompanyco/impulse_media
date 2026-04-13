<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class S3UrlService
{
    /**
     * Get a URL for an S3 object.
     * If a CloudFront CDN URL is configured, returns a fast CDN URL.
     * Otherwise, falls back to cached S3 signed URLs.
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

        // NOTE: CloudFront CDN is disabled because the distribution returns 403.
        // Using S3 signed URLs directly which always work with IAM credentials.
        // To re-enable CDN, fix the CloudFront OAI and S3 bucket policy first,
        // then uncomment the block below:
        //
        // $cdnUrl = config('filesystems.disks.s3.cdn_url') ?: env('AWS_CDN_URL');
        // if ($cdnUrl) {
        //     return rtrim($cdnUrl, '/') . '/' . ltrim($path, '/');
        // }

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
