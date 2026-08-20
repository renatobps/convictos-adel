<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricao_meta_configuracoes', function (Blueprint $table): void {
            if (! Schema::hasColumn('inscricao_meta_configuracoes', 'valor_com_camiseta')) {
                $table->decimal('valor_com_camiseta', 10, 2)->default(0)->after('valor_inscricao');
            }
            if (! Schema::hasColumn('inscricao_meta_configuracoes', 'valor_sem_camiseta')) {
                $table->decimal('valor_sem_camiseta', 10, 2)->default(0)->after('valor_com_camiseta');
            }
        });

        if (Schema::hasColumn('inscricao_meta_configuracoes', 'valor_com_camiseta')) {
            DB::table('inscricao_meta_configuracoes')->update([
                'valor_com_camiseta' => DB::raw('COALESCE(valor_inscricao, 0)'),
            ]);
        }

        Schema::table('inscricoes', function (Blueprint $table): void {
            if (! Schema::hasColumn('inscricoes', 'tipo_ingresso')) {
                $table->string('tipo_ingresso', 20)->default('com_camiseta')->after('idade');
            }
            if (! Schema::hasColumn('inscricoes', 'valor')) {
                $table->decimal('valor', 10, 2)->nullable()->after('tipo_ingresso');
            }
        });

        if (Schema::hasColumn('inscricoes', 'tipo_ingresso')) {
            $valorPadrao = (float) (DB::table('inscricao_meta_configuracoes')->value('valor_com_camiseta')
                ?? DB::table('inscricao_meta_configuracoes')->value('valor_inscricao')
                ?? 0);

            DB::table('inscricoes')
                ->whereNull('valor')
                ->update([
                    'tipo_ingresso' => 'com_camiseta',
                    'valor' => $valorPadrao,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table): void {
            if (Schema::hasColumn('inscricoes', 'valor')) {
                $table->dropColumn('valor');
            }
            if (Schema::hasColumn('inscricoes', 'tipo_ingresso')) {
                $table->dropColumn('tipo_ingresso');
            }
        });

        Schema::table('inscricao_meta_configuracoes', function (Blueprint $table): void {
            if (Schema::hasColumn('inscricao_meta_configuracoes', 'valor_sem_camiseta')) {
                $table->dropColumn('valor_sem_camiseta');
            }
            if (Schema::hasColumn('inscricao_meta_configuracoes', 'valor_com_camiseta')) {
                $table->dropColumn('valor_com_camiseta');
            }
        });
    }
};
