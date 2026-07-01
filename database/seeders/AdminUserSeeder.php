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
            ['email' => 'admin@app.test'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'password',
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
