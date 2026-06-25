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
            'users_manage',
            'roles_manage',
            'permissions_manage',
            'users_delete',
            'passwords_delete',
        ]);

        $admin->givePermissionTo([
            'users_manage',
            'passwords_delete',
        ]);
    }
}
