<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificacao_enquetes', function (Blueprint $table) {
            $table->text('mensagem_agradecimento')->nullable()->after('ativa');
        });

        Schema::table('enquete_respostas', function (Blueprint $table) {
            $table->foreignId('enquete_envio_id')->nullable()->after('enquete_id')->constrained('enquete_envios')->nullOnDelete();
            $table->string('nome_destinatario')->nullable()->after('destinatario');
            $table->unsignedTinyInteger('opcao_indice')->nullable()->after('resposta');
            $table->string('origem', 20)->nullable()->after('opcao_indice');
            $table->index(['enquete_id', 'resposta']);
            $table->index(['enquete_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('enquete_respostas', function (Blueprint $table) {
            $table->dropIndex(['enquete_id', 'resposta']);
            $table->dropIndex(['enquete_id', 'created_at']);
            $table->dropConstrainedForeignId('enquete_envio_id');
            $table->dropColumn(['nome_destinatario', 'opcao_indice', 'origem']);
        });

        Schema::table('notificacao_enquetes', function (Blueprint $table) {
            $table->dropColumn('mensagem_agradecimento');
        });
    }
};
