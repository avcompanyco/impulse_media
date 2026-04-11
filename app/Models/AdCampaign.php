<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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
     * Get the public URL for the media file.
     */
    public function mediaUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->media_path) {
                return '';
            }

            $disk = Storage::disk(getDisk());

            try {
                return $disk->temporaryUrl($this->media_path, now()->addHour());
            } catch (\Throwable $e) {
                return $disk->url($this->media_path);
            }
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
        if ($this->media_path) {
            Storage::disk(getDisk())->delete($this->media_path);
        }
    }
}
