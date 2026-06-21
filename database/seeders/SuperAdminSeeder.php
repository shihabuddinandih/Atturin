<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@atturin.com'],
            [
                'name' => 'Super Admin Atturin',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'phone' => '081111111111',
                'community_name' => 'Atturin HQ',
            ]
        );
    }
}
