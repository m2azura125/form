<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@app.test',
                'password' => 'password',
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['username' => 'krisna'],
            [
                'name' => 'Krisna',
                'email' => 'krisna@app.test',
                'password' => 'susjol123',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
