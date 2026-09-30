<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature('vmn:create-admin {email : Admin email address} {--name=Administrator : Display name} {--generate : Generate a one-time password instead of prompting}')]
#[Description('Create (or reset) a VM Newswire admin account')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('Please provide a valid email address.');

            return self::FAILURE;
        }

        $generate = (bool) $this->option('generate');
        $password = $generate ? Str::password(20) : (string) $this->secret('Password (min 12 characters, letters and numbers)');

        if (! $generate && Validator::make(['password' => $password], ['password' => ['required', Password::defaults()]])->fails()) {
            $this->error('Password must be at least 12 characters and include letters and numbers.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(['email' => $email], [
            'name' => $this->option('name'),
            'password' => $password,
            'role' => User::ROLE_ADMIN,
            'must_change_password' => $generate,
        ]);

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated')." admin {$email}.");

        if ($generate) {
            $this->warn('One-time password (must be changed at first login): '.$password);
        }

        return self::SUCCESS;
    }
}
