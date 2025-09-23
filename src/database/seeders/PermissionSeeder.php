<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = Permission::findOrCreate('upload-files', 'web');

        $role = Role::findOrCreate('admin', 'web');

        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }

        $user = \App\Models\User::find(1);
        if ($user && ! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }
    }
}
