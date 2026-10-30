<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cnpj_cpf', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('clientes')->whereNull('cnpj_cpf')->exists()) {
            throw new RuntimeException('Existem clientes sem CPF/CNPJ. Preencha os documentos antes de reverter esta migration.');
        }
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cnpj_cpf', 20)->nullable(false)->change();
        });
    }
};
