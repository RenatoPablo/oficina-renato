<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('veiculos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('cliente_veiculo', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('servicos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('estoques', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('servicos_ordem', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('pecas_ordem', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('cliente_veiculo', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('servicos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('estoques', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('servicos_ordem', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('pecas_ordem', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
