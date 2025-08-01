<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estoques', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });
    }

    public function down(): void
    {
        Schema::table('estoques', function (Blueprint $table) {
            $table->decimal('quantidade', 10, 2)->change();  // Ajuste para o tipo anterior, se for o caso
        });
    }
};
