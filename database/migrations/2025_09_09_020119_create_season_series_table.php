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
        Schema::create('season_series', function (Blueprint $table) {
            // el numero de la temporada, si es 1, 2, 3, etc. Se tomara en orden ascendente del id
            $table->id();

            $table->foreignId('serie_id')
                ->constrained('series');

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
        Schema::dropIfExists('season_series');
    }
};
