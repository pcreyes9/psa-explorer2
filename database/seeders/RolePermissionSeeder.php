<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::findByName('Super Admin');
        $admin = Role::findByName('Admin');
        $encoder = Role::findByName('Encoder');
        $viewer = Role::findByName('Viewer');

        $superAdmin->givePermissionTo([
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'delete-users',
            'reset-passwords',
        ]);

        $admin->givePermissionTo([
            'manage-users',
            'reset-passwords',
        ]);
    }
}