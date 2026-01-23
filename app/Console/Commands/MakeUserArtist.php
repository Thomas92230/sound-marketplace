<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Console\Command;

class MakeUserArtist extends Command
{
    protected $signature = 'user:make-artist {email}';
    protected $description = 'Promote a user to artist role';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->update(['role' => UserRole::Artist]);
        $this->info("User {$email} is now an artist!");
        
        return 0;
    }
}