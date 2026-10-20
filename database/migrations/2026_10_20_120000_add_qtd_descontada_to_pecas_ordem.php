<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estoques', function (Blueprint $table) {
            $table->decimal('quantidade', 15, 2)->nullable()->change();
        });
        Schema::table('pecas_ordem', function (Blueprint $table) {
            $table->decimal('qtd_descontada', 10, 2)->default(0);
        });
        // A regra anterior só aceitava linhas com desconto integral.
        DB::table('pecas_ordem')->whereNull('deleted_at')->update(['qtd_descontada' => DB::raw('qtd')]);
    }

    public function down(): void
    {
        // Mantém a precisão do saldo para não truncar reposições e consumos fracionários.
        Schema::table('pecas_ordem', fn (Blueprint $table) => $table->dropColumn('qtd_descontada'));
    }
};
