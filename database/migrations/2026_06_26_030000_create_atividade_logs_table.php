<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atividade_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('usuario_nome');
            $table->string('usuario_email')->nullable();
            $table->text('descricao');
            $table->string('acao', 50)->nullable();
            $table->string('entidade_tipo')->nullable();
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->json('detalhes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['entidade_tipo', 'entidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atividade_logs');
    }
};
