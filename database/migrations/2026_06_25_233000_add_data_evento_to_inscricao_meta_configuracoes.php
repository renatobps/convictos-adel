<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('inscricao_meta_configuracoes', 'data_evento')) {
            Schema::table('inscricao_meta_configuracoes', function (Blueprint $table) {
                $table->date('data_evento')->nullable()->after('valor_inscricao');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inscricao_meta_configuracoes', 'data_evento')) {
            Schema::table('inscricao_meta_configuracoes', function (Blueprint $table) {
                $table->dropColumn('data_evento');
            });
        }
    }
};
