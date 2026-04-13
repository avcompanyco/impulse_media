<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Services\S3UrlService;

class AdCampaign extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'media_path',
        'media_type',
        'status',
        'impressions_count',
    ];

    protected $appends = [
        'media_url',
    ];

    /**
     * Get the media items for this campaign.
     */
    public function mediaItems()
    {
        return $this->hasMany(AdCampaignMedia::class, 'ad_campaign_id');
    }

    /**
     * Get the public URL for the legacy media file.
     */
    public function mediaUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->media_path) {
                return '';
            }

            return S3UrlService::temporaryUrl($this->media_path) ?? '';
        });
    }

    /**
     * Scope for active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Delete the media file from storage when campaign is deleted.
     */
    public function deleteMedia(): void
    {
        // Delete legacy single media
        if ($this->media_path && $this->media_path !== '0') {
            Storage::disk(getDisk())->delete($this->media_path);
            S3UrlService::forgetUrl($this->media_path);
        }

        // Delete all media items from S3
        foreach ($this->mediaItems as $media) {
            if ($media->media_path && $media->media_path !== '0') {
                Storage::disk(getDisk())->delete($media->media_path);
                S3UrlService::forgetUrl($media->media_path);
            }
        }

        // Delete all media item records
        $this->mediaItems()->delete();
    }
}
