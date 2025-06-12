<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estoques', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('codigo_barras')->nullable();
            $table->string('descricao');
            $table->string('medida', 10)->nullable();
            $table->decimal('preco_rs', 10, 2)->default(0.01);
            $table->decimal('preco_usd', 10, 2)->nullable();
            $table->decimal('custo_compra', 10, 2)->nullable();
            $table->decimal('custo_medio', 10, 2)->nullable();
            $table->decimal('quantidade', 10, 2)->nullable();
            $table->decimal('qtd_minima', 10, 2)->nullable();
            $table->decimal('comissao', 10, 2)->nullable();
            $table->decimal('lucro', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoques');
    }
};
