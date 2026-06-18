<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'delete-users',
            'reset-passwords',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }
    }
}