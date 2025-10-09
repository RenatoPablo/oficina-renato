<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = [
        // pais
        'clientes','veiculos','estoques','servicos','ordens_servico',
        // filhos/itens
        'pecas_ordem','servicos_ordem','cliente_veiculo',
    ];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            if (!Schema::hasColumn($t, 'user_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            if (Schema::hasColumn($t, 'user_id')) {
                Schema::table($t, function (Blueprint $table) {
                    // nome padrão do FK pode variar por ambiente; garantimos com ambos
                    try { $table->dropForeign([$table->getTable().'_user_id_foreign']); } catch (\Throwable $e) {}
                    try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
                    $table->dropColumn('user_id');
                });
            }
        }
    }
};
