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
        Schema::table('movies', function (Blueprint $table) {
            $table->string('trailer_video')->nullable()->change();
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('trailer_video')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('trailer_video')->nullable(false)->change();
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('trailer_video')->nullable(false)->change();
        });
    }
};
