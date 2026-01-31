<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fullName = env('SUPER_ADMIN_FULL_NAME');
        $username = env('SUPER_ADMIN_USERNAME');
        $password = env('SUPER_ADMIN_PASSWORD');
        $role = env('SUPER_ADMIN_ROLE');

        Admin::updateOrCreate(
            ['id' => 1],
            [
                'full_name' => $fullName,
                'username'  => $username,
                'password'  => Hash::make($password),
                'role'      => $role,
            ]
        );
    }
}
