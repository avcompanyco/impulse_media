<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('categories')->insertOrIgnore([
            ['name' => 'Series', 'image' => '', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Movies', 'image' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')->whereIn('name', ['Series', 'Movies'])->delete();
    }
};
