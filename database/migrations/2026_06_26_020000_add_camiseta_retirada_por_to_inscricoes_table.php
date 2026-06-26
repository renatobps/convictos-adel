<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table): void {
            if (! Schema::hasColumn('inscricoes', 'camiseta_retirada_por')) {
                $table->string('camiseta_retirada_por')->nullable()->after('camiseta_retirada_em');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table): void {
            if (Schema::hasColumn('inscricoes', 'camiseta_retirada_por')) {
                $table->dropColumn('camiseta_retirada_por');
            }
        });
    }
};
