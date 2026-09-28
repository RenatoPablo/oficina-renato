<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class testeLoginUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [ 'email' => 'testeLogin@gmail.com' ],
            [
                'name' => 'Teste de Login',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'is_admin' => true // descomenta se tiver essa coluna
            ]
        );
    }
}
