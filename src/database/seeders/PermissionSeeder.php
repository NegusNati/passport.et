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
        // Create or find permissions
        $uploadPermission = Permission::findOrCreate('upload-files', 'web');
        $manageArticlesPermission = Permission::findOrCreate('manage-articles', 'web');
        $manageAdsPermission = Permission::findOrCreate('manage-advertisements', 'web');

        // Create or find roles
        $adminRole = Role::findOrCreate('admin', 'web');
        $editorRole = Role::findOrCreate('editor', 'web');
        $userRole = Role::findOrCreate('user', 'web');

        // Give permissions to admin role
        if (! $adminRole->hasPermissionTo($uploadPermission)) {
            $adminRole->givePermissionTo($uploadPermission);
        }
        if (! $adminRole->hasPermissionTo($manageArticlesPermission)) {
            $adminRole->givePermissionTo($manageArticlesPermission);
        }
        if (! $adminRole->hasPermissionTo($manageAdsPermission)) {
            $adminRole->givePermissionTo($manageAdsPermission);
        }

        // Give permissions to editor role
        if (! $editorRole->hasPermissionTo($manageArticlesPermission)) {
            $editorRole->givePermissionTo($manageArticlesPermission);
        }

        $userAtThisNumber = 1;

        // Assign admin role to first user
        $user = User::find($userAtThisNumber);

        if ($user) {
            if (! $user->hasRole($adminRole->name)) {
                $user->assignRole($adminRole);
                $this->command->info("Admin role assigned to User #{$userAtThisNumber} ({$user->email}).");
            } else {
                $this->command->info("User #{$userAtThisNumber} ({$user->email}) already has the admin role.");
            }
        } else {
            $this->command->warn("No user with ID {$userAtThisNumber} found, skipping admin assignment.");
        }
    }
}
