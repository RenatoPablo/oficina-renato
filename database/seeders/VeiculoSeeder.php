<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Veiculo;
use Faker\Factory as Faker;

class VeiculoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        for ($i = 0; $i < 10; $i++) {
            Veiculo::create([
                'tipo'   => $faker->randomElement(['Carro', 'Moto', 'Caminhão']),
                'marca'  => $faker->company,
                'modelo' => $faker->word,
                'placa'  => strtoupper($faker->bothify('???-####')),
                'km'     => $faker->numberBetween(0, 300000),
                'ano'    => $faker->numberBetween(1970, 2025),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
