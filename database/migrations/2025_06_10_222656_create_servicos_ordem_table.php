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
        Schema::create('servicos_ordem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->onDelete('cascade');
            $table->foreignId('servico_id')->constrained('servicos');
            $table->integer('qtd')->default(1);
            $table->decimal('valor_unit', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->string('tecnico')->nullable();
            $table->string('codigo_cor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicos_ordem');
    }
};
