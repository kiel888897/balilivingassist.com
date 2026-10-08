<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'bla:admin {--reset : Reset the password for an existing admin email}';

    protected $description = 'Create the first BLA admin account';

    public function handle()
    {
        $isReset = $this->option('reset');
        $name = $isReset ? null : trim($this->ask('Admin name'));
        $email = strtolower(trim($this->ask('Admin email')));

        if ((!$isReset && $name === '') || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid name and email address are required.');

            return 1;
        }

        $user = User::where('email', $email)->first();
        $superAdminRole = Role::where('slug', 'super_admin')->first();

        if (!$superAdminRole) {
            $this->error('Admin roles are not initialized. Run artisan migrate --seed first.');

            return 1;
        }

        if ($isReset && !$user) {
            $this->error('No account exists with this email address.');

            return 1;
        }

        if (!$isReset && $user) {
            $this->error('An account with this email already exists.');

            return 1;
        }

        $this->warn('Password input will be visible in this terminal.');
        $password = $this->ask('Admin password (minimum 12 characters)');
        $confirmation = $this->ask('Confirm password');

        if (strlen($password) < 12 || $password !== $confirmation) {
            $this->error('Passwords must match and be at least 12 characters long.');

            return 1;
        }

        if ($isReset) {
            if (!$user->roles()->where('slug', 'super_admin')->exists()) {
                $this->error('This account is not assigned the super_admin role.');

                return 1;
            }

            $user->password = Hash::make($password);
            $user->save();
            $this->info('Admin password updated. You can now sign in at /login.');
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
            $this->info('Admin account created. You can now sign in at /login.');
        }

        return 0;
    }
}
