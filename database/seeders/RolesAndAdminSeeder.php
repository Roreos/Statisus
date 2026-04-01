<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $admin  = Role::firstOrCreate(['name' => 'admin',  'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Create first admin if no users exist
        if (User::count() === 0) {
            $user = User::create([
                'name'     => env('ADMIN_NAME', 'Admin'),
                'email'    => env('ADMIN_EMAIL', 'admin@example.com'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'timezone' => 'UTC',
            ]);

            $user->assignRole($admin);

            $this->command->info("Admin created: {$user->email}");
        } else {
            // Ensure existing users without a role get admin
            User::all()->each(function (User $user) use ($admin) {
                if ($user->roles->isEmpty()) {
                    $user->assignRole($admin);
                }
            });
        }
    }
}
