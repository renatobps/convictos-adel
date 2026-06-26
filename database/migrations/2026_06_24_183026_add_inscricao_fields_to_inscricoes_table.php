<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            if (! Schema::hasColumn('inscricoes', 'tamanho_camiseta')) {
                $table->string('tamanho_camiseta', 4)->nullable()->after('idade');
            }
            if (! Schema::hasColumn('inscricoes', 'lider_jovens')) {
                $table->boolean('lider_jovens')->default(false)->after('igreja');
            }
        });

        if (! Schema::hasColumn('inscricoes', 'igreja_id')) {
            Schema::table('inscricoes', function (Blueprint $table) {
                $table->foreignId('igreja_id')->nullable()->after('igreja')->constrained('igrejas')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            if (Schema::hasColumn('inscricoes', 'igreja_id')) {
                $table->dropConstrainedForeignId('igreja_id');
            }
            if (Schema::hasColumn('inscricoes', 'tamanho_camiseta')) {
                $table->dropColumn('tamanho_camiseta');
            }
            if (Schema::hasColumn('inscricoes', 'lider_jovens')) {
                $table->dropColumn('lider_jovens');
            }
        });
    }
};
