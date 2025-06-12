<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // cliente_veiculo
        Schema::table('cliente_veiculo', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['veiculo_id']);
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('veiculo_id')->references('id')->on('veiculos');
        });

        // ordens_servico
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['veiculo_id']);
            $table->foreign('veiculo_id')->references('id')->on('veiculos');
        });

        // servicos_ordem
        Schema::table('servicos_ordem', function (Blueprint $table) {
            $table->dropForeign(['ordem_servico_id']);
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico');
        });

        // pecas_ordem
        Schema::table('pecas_ordem', function (Blueprint $table) {
            $table->dropForeign(['ordem_servico_id']);
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico');
        });
    }

    public function down(): void {
        // Restaura com cascade
        Schema::table('cliente_veiculo', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['veiculo_id']);
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('veiculo_id')->references('id')->on('veiculos')->onDelete('cascade');
        });

        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['veiculo_id']);
            $table->foreign('veiculo_id')->references('id')->on('veiculos')->onDelete('cascade');
        });

        Schema::table('servicos_ordem', function (Blueprint $table) {
            $table->dropForeign(['ordem_servico_id']);
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->onDelete('cascade');
        });

        Schema::table('pecas_ordem', function (Blueprint $table) {
            $table->dropForeign(['ordem_servico_id']);
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->onDelete('cascade');
        });
    }
};
?>