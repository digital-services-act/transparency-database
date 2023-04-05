<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class GiveRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'give-role {role} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give the role to a user identified by email.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $role = Role::findByName($this->argument('role'));
        /** @var User $user */
        $user = User::where('email', $this->argument('email'))->first();

        if ($role && $user) {
            $user->assignRole($role);
            $this->info('The role was given to the user.');
        } else {
            $this->error('The role or user was not found.');
        }
    }
}
