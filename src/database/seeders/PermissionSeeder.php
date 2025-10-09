<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or find permission
        $permission = Permission::findOrCreate('upload-files', 'web');

        // Create or find role
        $role = Role::findOrCreate('admin', 'web');

        // Give permission to role if not already assigned
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }

        $userAtThisNumber = 1;

        // Assign role to first user
        $user = User::find($userAtThisNumber);

        if ($user) {
            if (! $user->hasRole($role->name)) {
                $user->assignRole($role);
                $this->command->info("Admin role assigned to User #{$userAtThisNumber} ({$user->email}).");
            } else {
                $this->command->info("User #{$userAtThisNumber} ({$user->email}) already has the admin role.");
            }
        } else {
            $this->command->warn("No user with ID {$userAtThisNumber} found, skipping admin assignment.");
        }
    }
}
