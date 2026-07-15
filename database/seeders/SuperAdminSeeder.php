<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
    */
    public function run(): void
    {
        // Creating Super Admin User
        $superAdmin = User::create([
            'name' => 'Ki Kalbar',
            'email' => 'superadmin',
            'password' => Hash::make('super123')
        ]);
        $superAdmin->assignRole('Super Admin');

        // Creating Admin User
        $admin = User::create([
            'name' => 'Verifikator',
            'email' => 'verif1',
            'password' => Hash::make('verifikator')
        ]);
        $admin->assignRole('Admin');

        // Creating Product Manager User
        $productManager = User::create([
            'name' => 'Fahri',
            'email' => 'bptes@roles.id',
            'password' => Hash::make('tes1234')
        ]);
        $productManager->assignRole('Badan Publik');
    }
}
