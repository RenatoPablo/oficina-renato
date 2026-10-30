<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pecas_ordem', 'servicos_ordem'] as $nome) {
            Schema::table($nome, function (Blueprint $table) {
                $table->decimal('valor_unit', 15, 5)->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['pecas_ordem', 'servicos_ordem'] as $nome) {
            Schema::table($nome, function (Blueprint $table) {
                $table->decimal('valor_unit', 10, 2)->change();
            });
        }
    }
};
