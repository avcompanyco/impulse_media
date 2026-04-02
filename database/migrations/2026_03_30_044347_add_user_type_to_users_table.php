<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type')->default('creator')->after('status');
            $table->timestamp('accepted_terms_at')->nullable()->after('user_type');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('plan_type')->default('creator')->after('status');
            $table->boolean('has_ads')->default(false)->after('plan_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'accepted_terms_at']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['plan_type', 'has_ads']);
        });
    }
};
