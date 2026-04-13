<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\S3UrlService;

class AdCampaignMedia extends Model
{
    protected $table = 'ad_campaign_media';

    protected $fillable = [
        'ad_campaign_id',
        'media_path',
        'media_type',
    ];

    protected $appends = [
        'media_url',
    ];

    /**
     * Get the campaign this media belongs to.
     */
    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    /**
     * Get the public URL for the media file.
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
     * Delete the media file from storage.
     */
    public function deleteMedia(): void
    {
        if ($this->media_path) {
            \Illuminate\Support\Facades\Storage::disk(getDisk())->delete($this->media_path);
            S3UrlService::forgetUrl($this->media_path);
        }
    }
}
