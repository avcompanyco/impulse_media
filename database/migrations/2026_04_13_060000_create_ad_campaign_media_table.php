<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_campaign_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->onDelete('cascade');
            $table->string('media_path');
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->timestamps();
        });

        // Migrate existing campaign media into the new table
        $campaigns = DB::table('ad_campaigns')->whereNotNull('media_path')->where('media_path', '!=', '')->get();
        foreach ($campaigns as $campaign) {
            DB::table('ad_campaign_media')->insert([
                'ad_campaign_id' => $campaign->id,
                'media_path' => $campaign->media_path,
                'media_type' => $campaign->media_type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_campaign_media');
    }
};
