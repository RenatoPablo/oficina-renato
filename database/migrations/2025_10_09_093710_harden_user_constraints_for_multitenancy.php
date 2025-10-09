<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 3.1) tornar user_id obrigatório
        foreach ([
            'clientes','veiculos','estoques','servicos','ordens_servico',
            'pecas_ordem','servicos_ordem','cliente_veiculo',
        ] as $t) {
            if (Schema::hasColumn($t, 'user_id')) {
                Schema::table($t, fn (Blueprint $tb) => $tb->unsignedBigInteger('user_id')->nullable(false)->change());
            }
        }

        // 3.2) trocar uniques globais por "por usuário"

        // clientes: cnpj_cpf único por usuário
        $this->dropUniqueIfExists('clientes', 'clientes_cnpj_cpf_unique');
        Schema::table('clientes', function (Blueprint $table) {
            $this->addUniqueIfNotExists('clientes', 'clientes_user_cnpj_unique', ['user_id','cnpj_cpf']);
        });

        // veiculos: placa único por usuário
        $this->dropUniqueIfExists('veiculos', 'veiculos_placa_unique');
        Schema::table('veiculos', function (Blueprint $table) {
            $this->addUniqueIfNotExists('veiculos', 'veiculos_user_placa_unique', ['user_id','placa']);
        });

        // estoques: codigo único por usuário (coluna já é nullable)
        $this->dropUniqueIfExists('estoques', 'estoques_codigo_unique');
        Schema::table('estoques', function (Blueprint $table) {
            $this->addUniqueIfNotExists('estoques', 'estoques_user_codigo_unique', ['user_id','codigo']);
        });

        // servicos: (opcional) descricao único por usuário
        Schema::table('servicos', function (Blueprint $table) {
            $this->addUniqueIfNotExists('servicos', 'servicos_user_desc_unique', ['user_id','descricao']);
        });

        // cliente_veiculo: evita duplicar o mesmo vínculo
        Schema::table('cliente_veiculo', function (Blueprint $table) {
            $this->addUniqueIfNotExists('cliente_veiculo', 'cliente_veiculo_user_cv_unique', ['user_id','cliente_id','veiculo_id']);
        });
    }

    public function down(): void
    {
        // remover uniques novos (rollback)
        $this->dropUniqueIfExists('clientes', 'clientes_user_cnpj_unique');
        $this->dropUniqueIfExists('veiculos', 'veiculos_user_placa_unique');
        $this->dropUniqueIfExists('estoques', 'estoques_user_codigo_unique');
        $this->dropUniqueIfExists('servicos', 'servicos_user_desc_unique');
        $this->dropUniqueIfExists('cliente_veiculo', 'cliente_veiculo_user_cv_unique');

        // recriar os antigos globais principais
        Schema::table('clientes', fn (Blueprint $t) => $t->unique('cnpj_cpf','clientes_cnpj_cpf_unique'));
        Schema::table('veiculos', fn (Blueprint $t) => $t->unique('placa','veiculos_placa_unique'));
        Schema::table('estoques', fn (Blueprint $t) => $t->unique('codigo','estoques_codigo_unique'));

        // tornar user_id nullable de novo (se necessário)
        foreach ([
            'clientes','veiculos','estoques','servicos','ordens_servico',
            'pecas_ordem','servicos_ordem','cliente_veiculo',
        ] as $t) {
            if (Schema::hasColumn($t,'user_id')) {
                Schema::table($t, fn (Blueprint $tb) => $tb->unsignedBigInteger('user_id')->nullable()->change());
            }
        }
    }

    // helpers
    private function dropUniqueIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            Schema::table($table, fn (Blueprint $t) => $t->dropUnique($index));
        }
    }

    private function addUniqueIfNotExists(string $table, string $index, array $columns): void
    {
        if (!$this->indexExists($table, $index)) {
            Schema::table($table, fn (Blueprint $t) => $t->unique($columns, $index));
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $res = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
        return !empty($res);
    }
};
