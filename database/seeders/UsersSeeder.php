<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminCredentials = [
            'email' => 'superadmin@shopify.com',
            'password' => Hash::make('123456'),
            'name' => 'SuperAdmin'
        ];

        $superAdmin = User::updateOrCreate([
            'email' => 'superadmin@shopify.com'
        ], $superAdminCredentials);

        $superAdmin->assignRole('SuperAdmin');
        $superAdmin->givePermissionTo('all-access');

        $user = User::where('email', 'sid.sjv@gmail.com')->first();
        $user->assignRole('Admin');
    }
}
