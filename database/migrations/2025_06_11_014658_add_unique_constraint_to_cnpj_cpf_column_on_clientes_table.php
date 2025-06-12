<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cnpj_cpf', 20)->unique()->change();
        });
    }

    public function down(): void {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['cnpj_cpf']);
            $table->string('cnpj_cpf', 20)->change(); // remove unique
        });
    }
};
