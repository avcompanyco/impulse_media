<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'admin',
            'email' => 'admin@example.com',
        ]);

        Role::create(['name' => 'user']);
        Role::create(['name' => 'admin']);

        $user->assignRole('admin');

        $user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'user',
            'email' => 'user@example.com',
        ]);

        $user->assignRole('user');

        $this->call([
            CategorySeeder::class,
            PlanSeeder::class,
            TestContentSeeder::class,
        ]);
    }
}
