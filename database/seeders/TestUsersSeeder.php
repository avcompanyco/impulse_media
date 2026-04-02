<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'spectator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'creator', 'guard_name' => 'web']);

        // ─── 1. Creator Test User ───
        $creatorPlan = Plan::where('plan_type', 'creator')->first();

        $creator = User::updateOrCreate(
            ['email' => 'creator@test.com'],
            [
                'name' => 'Test Creator',
                'username' => 'testcreator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'creator',
                'accepted_terms_at' => now(),
                'plan_id' => $creatorPlan?->id,
            ]
        );

        // Sync roles (clears old roles, assigns new ones)
        $creator->syncRoles(['user', 'creator']);
        $this->command->info("Creator user created:");
        $this->command->info("  Email: creator@test.com");
        $this->command->info("  Password: password");
        $this->command->info("  Plan: " . ($creatorPlan?->name ?? 'None'));

        // ─── 2. Spectator Test User (Standard Plan - with ads) ───
        $spectatorStandardPlan = Plan::where('plan_type', 'spectator')
            ->where('has_ads', true)
            ->first();

        $spectatorStandard = User::updateOrCreate(
            ['email' => 'spectator@test.com'],
            [
                'name' => 'Test Spectator',
                'username' => 'testspectator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'spectator',
                'accepted_terms_at' => now(),
                'plan_id' => $spectatorStandardPlan?->id,
            ]
        );

        $spectatorStandard->syncRoles(['spectator']);
        $this->command->info("Spectator (Standard) user created:");
        $this->command->info("  Email: spectator@test.com");
        $this->command->info("  Password: password");
        $this->command->info("  Plan: " . ($spectatorStandardPlan?->name ?? 'None') . " (with ads)");

        // ─── 3. Spectator Premium Test User (no ads) ───
        $spectatorPremiumPlan = Plan::where('plan_type', 'spectator')
            ->where('has_ads', false)
            ->first();

        $spectatorPremium = User::updateOrCreate(
            ['email' => 'spectatorpremium@test.com'],
            [
                'name' => 'Test Spectator Premium',
                'username' => 'testspectatorpremium',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'spectator',
                'accepted_terms_at' => now(),
                'plan_id' => $spectatorPremiumPlan?->id,
            ]
        );

        $spectatorPremium->syncRoles(['spectator']);
        $this->command->info("Spectator (Premium) user created:");
        $this->command->info("  Email: spectatorpremium@test.com");
        $this->command->info("  Password: password");
        $this->command->info("  Plan: " . ($spectatorPremiumPlan?->name ?? 'None') . " (no ads)");

        $this->command->newLine();
        $this->command->info("All test users created successfully!");
    }
}
