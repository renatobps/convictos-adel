<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacao_grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo'); // igreja, regional, inscritos
            $table->foreignId('igreja_id')->nullable()->constrained('igrejas')->nullOnDelete();
            $table->foreignId('regional_id')->nullable()->constrained('regionais')->nullOnDelete();
            $table->string('status_inscricao')->nullable();
            $table->boolean('sistema')->default(false);
            $table->timestamps();
        });

        Schema::create('configuracoes_mensagens', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->string('titulo');
            $table->text('conteudo');
            $table->string('imagem_url')->nullable();
            $table->timestamps();
        });

        Schema::create('notificacao_enquetes', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('pergunta');
            $table->json('opcoes');
            $table->boolean('ativa')->default(true);
            $table->foreignId('notificacao_grupo_id')->nullable()->constrained('notificacao_grupos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('enquete_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquete_id')->constrained('notificacao_enquetes')->cascadeOnDelete();
            $table->string('destinatario');
            $table->string('nome_destinatario')->nullable();
            $table->string('status')->default('enviada');
            $table->timestamps();
        });

        Schema::create('enquete_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquete_id')->constrained('notificacao_enquetes')->cascadeOnDelete();
            $table->string('destinatario');
            $table->string('resposta');
            $table->timestamps();
        });

        Schema::create('notificacoes_enviadas', function (Blueprint $table) {
            $table->id();
            $table->string('destinatario');
            $table->text('mensagem');
            $table->string('status')->default('enviada');
            $table->string('tipo_envio')->nullable();
            $table->foreignId('notificacao_grupo_id')->nullable()->constrained('notificacao_grupos')->nullOnDelete();
            $table->foreignId('inscricao_id')->nullable()->constrained('inscricoes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes_enviadas');
        Schema::dropIfExists('enquete_respostas');
        Schema::dropIfExists('enquete_envios');
        Schema::dropIfExists('notificacao_enquetes');
        Schema::dropIfExists('configuracoes_mensagens');
        Schema::dropIfExists('notificacao_grupos');
    }
};
