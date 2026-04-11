<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('media_path'); // S3 path to image or video
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_campaigns');
    }
};
