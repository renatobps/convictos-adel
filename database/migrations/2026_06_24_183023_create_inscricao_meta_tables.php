<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscricao_meta_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('meta_total')->default(500);
            $table->decimal('valor_inscricao', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('inscricao_meta_regionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regional_id')->unique()->constrained('regionais')->cascadeOnDelete();
            $table->unsignedInteger('meta')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscricao_meta_regionais');
        Schema::dropIfExists('inscricao_meta_configuracoes');
    }
};
