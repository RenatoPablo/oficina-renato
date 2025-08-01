<?php

namespace Database\Seeders;

use App\Models\Estoque;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstoqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Estoque::create([
            'codigo' => '40158',
            'descricao' => 'Abraçadeira',
            'quantidade' => '60',
            'preco_rs' => '59.60',
            'medida' => 'Unidade'
        ]);

        Estoque::create([
            'codigo' => '40157',
            'descricao' => 'Oleo 20w50',
            'quantidade' => '60',
            'preco_rs' => '39.60',
            'medida' => 'Litro'
        ]);

        Estoque::create([
            'codigo' => '40159',
            'descricao' => 'Amortecedor',
            'quantidade' => '30',
            'preco_rs' => '159.60',
            'medida' => 'Unidade'
        ]);

        Estoque::create([
            'codigo' => '40160',
            'descricao' => 'Jogo de Junta Motor',
            'quantidade' => '35',
            'preco_rs' => '67.60',
            'medida' => 'Kit'
        ]);

        Estoque::create([
            'codigo' => '40161',
            'descricao' => 'Pistão do motor',
            'quantidade' => '15',
            'preco_rs' => '250.60',
            'medida' => 'Jogo'
        ]);

        Estoque::create([
            'codigo' => '40162',
            'descricao' => 'Filtro de oleo',
            'quantidade' => '60',
            'preco_rs' => '59.60',
            'medida' => 'Unidade'
        ]);

        Estoque::create([
            'codigo' => '40163',
            'descricao' => 'Filtro de Ar',
            'quantidade' => '60',
            'preco_rs' => '59.60',
            'medida' => 'Unidade'
        ]);

        Estoque::create([
            'codigo' => '40164',
            'descricao' => 'Coifa Suspensão',
            'quantidade' => '60',
            'preco_rs' => '59.60',
            'medida' => 'Unidade'
        ]);

        Estoque::create([
            'codigo' => '40165',
            'descricao' => 'Rolamento roda',
            'quantidade' => '60',
            'preco_rs' => '59.60',
            'medida' => 'Unidade'
        ]);

        Estoque::create([
            'codigo' => '40166',
            'descricao' => 'Correia alternador',
            'quantidade' => '60',
            'preco_rs' => '59.60',
            'medida' => 'Unidade'
        ]);

        Estoque::create([
            'codigo' => '40167',
            'descricao' => 'Polia tensionadora',
            'quantidade' => '60',
            'preco_rs' => '59.60',
            'medida' => 'Unidade'
        ]);
    }
}
