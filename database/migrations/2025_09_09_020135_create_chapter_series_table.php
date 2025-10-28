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
        Schema::create('chapter_series', function (Blueprint $table) {
            $table->id();

            $table->integer('chapter_number');
            $table->string('title');
            $table->string('thumbnail');
            $table->string('chapter_video');
            $table->string('status');

            $table->foreignId('season_id')
                ->constrained('season_series');

            $table->foreignId('user_id')
                ->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_series');
    }
};
