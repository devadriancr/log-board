<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Can be run again without duplicating anything.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => Hash::make('password')],
        );

        // Crea los permisos y los roles.
        $this->call(RolesAndPermissionsSeeder::class);

        // Esta cuenta es el superadministrador.
        $admin->assignRole('super-admin');
    }
}
