<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('placa')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('veiculos')->whereNull('placa')->exists()) {
            throw new RuntimeException('Existem veículos sem placa. Preencha as placas antes de reverter esta migration.');
        }
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('placa')->nullable(false)->change();
        });
    }
};
