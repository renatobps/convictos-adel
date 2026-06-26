<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table): void {
            if (! Schema::hasColumn('inscricoes', 'camiseta_retirada')) {
                $table->boolean('camiseta_retirada')->default(false)->after('tamanho_camiseta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table): void {
            if (Schema::hasColumn('inscricoes', 'camiseta_retirada')) {
                $table->dropColumn('camiseta_retirada');
            }
        });
    }
};
