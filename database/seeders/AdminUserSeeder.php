<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // busca por email
            [
                'name' => 'Administrador',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                // 'is_admin' => true, // descomenta se tiver essa coluna
            ]
        );
    }
}