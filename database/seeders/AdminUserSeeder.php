<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('paclab.seed_admin_email');

        if (! User::where('email', $email)->exists()) {
            User::create([
                'name' => 'PacLab Administrator',
                'email' => $email,
                'password' => config('paclab.seed_admin_password'),
                'role' => 'admin',
                'is_active' => true,
            ]);
            $this->command?->warn("Admin login created: $email / ".config('paclab.seed_admin_password').'  (change it after first login)');
        }
    }
}
