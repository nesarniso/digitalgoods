<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
        {email : Administrator email address}
        {--name=Administrator : Administrator display name}
        {--password= : Password; if omitted, prompt securely}';

    protected $description = 'Create or promote an administrator account';

    public function handle(): int
    {
        $password = $this->option('password') ?: $this->secret('Password');

        if (! is_string($password) || strlen($password) < 8) {
            $this->error('The password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::query()->firstOrNew(['email' => $this->argument('email')]);
        $user->name = $this->option('name');
        $user->uuid ??= (string) Str::uuid();
        $user->password = Hash::make($password);
        $user->email_verified_at ??= now();
        $user->save();
        $user->assignRole('super_admin');

        $this->info("Administrator account ready: {$user->email}");

        return self::SUCCESS;
    }
}
