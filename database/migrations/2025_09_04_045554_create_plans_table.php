<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description');
            $table->decimal('price', 20, 2);
            $table->string('billing_period');
            $table->integer('free_days_trial');
            $table->boolean('is_unlimited_content');
            $table->integer('movies_upload_count');
            $table->integer('series_upload_count');
            $table->integer('shorts_upload_count');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
