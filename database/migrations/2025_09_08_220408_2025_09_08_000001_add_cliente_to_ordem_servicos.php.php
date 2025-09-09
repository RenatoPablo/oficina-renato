<?php

// database/migrations/2025_09_08_000001_add_cliente_to_ordem_servicos.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('ordens_servico', function (Blueprint $table) {
      $table->foreignId('cliente_id')->nullable()->constrained('clientes');
      $table->foreignId('cliente_veiculo_id')->nullable()->constrained('cliente_veiculo');
      // snapshots (opcional, mas recomendado pra “nota”/compliance)
      $table->string('cliente_nome_snapshot')->nullable();
      $table->string('cliente_documento_snapshot', 20)->nullable();
    });
  }

  public function down(): void {
    Schema::table('ordens_servico', function (Blueprint $table) {
      $table->dropConstrainedForeignId('cliente_id');
      $table->dropConstrainedForeignId('cliente_veiculo_id');
      $table->dropColumn(['cliente_nome_snapshot', 'cliente_documento_snapshot']);
    });
  }
};
