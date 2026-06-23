<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {

        $user = User::updateOrCreate(
            [
                'username' => 'admin',
            ],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@psaexplorer.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $user->assignRole('Super Admin');
        }
}