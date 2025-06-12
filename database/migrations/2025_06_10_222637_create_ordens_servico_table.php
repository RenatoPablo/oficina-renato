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
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->onDelete('cascade');
            $table->dateTime('data_chamado');
            $table->dateTime('data_previsao_entrega')->nullable();
            $table->string('tipo_atendimento')->nullable();
            $table->string('situacao')->default('Aberta');
            $table->string('atendente')->nullable();
            $table->text('problema_reclamado')->nullable();
            $table->string('revisao_ate')->nullable();
            $table->decimal('frete', 10, 2)->default(0);
            $table->decimal('total_servicos', 10, 2)->default(0);
            $table->decimal('total_pecas', 10, 2)->default(0);
            $table->decimal('total_os', 10, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
