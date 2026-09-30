<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the first admin from ADMIN_EMAIL / ADMIN_PASSWORD.
 * Without ADMIN_PASSWORD a random password is generated, printed once,
 * and the admin must change it on first login. No password is hardcoded.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('vmnewswire.admin.email');

        if (blank($email)) {
            $this->command?->warn('ADMIN_EMAIL is not set: no admin created. Run `php artisan vmn:create-admin` later.');

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} already exists.");

            return;
        }

        $password = config('vmnewswire.admin.password');
        $generated = blank($password);
        $password = $generated ? Str::password(20) : $password;

        User::create([
            'name' => config('vmnewswire.admin.name'),
            'email' => $email,
            'password' => $password,
            'role' => User::ROLE_ADMIN,
            'must_change_password' => $generated,
        ]);

        if ($generated) {
            $this->command?->warn("Admin {$email} created with a one-time password (change it on first login):");
            $this->command?->line("  {$password}");
        } else {
            $this->command?->info("Admin {$email} created.");
        }
    }
}
