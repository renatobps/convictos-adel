<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('inscricoes', 'codigo')) {
            Schema::table('inscricoes', function (Blueprint $table) {
                $table->string('codigo', 20)->nullable()->unique()->after('id');
            });
        }

        DB::table('inscricoes')
            ->whereNull('codigo')
            ->orWhere('codigo', '')
            ->get(['id'])
            ->each(function ($row): void {
                DB::table('inscricoes')
                    ->where('id', $row->id)
                    ->update(['codigo' => $this->gerarCodigo()]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('inscricoes', 'codigo')) {
            Schema::table('inscricoes', function (Blueprint $table) {
                $table->dropUnique(['codigo']);
                $table->dropColumn('codigo');
            });
        }
    }

    private function gerarCodigo(): string
    {
        do {
            $codigo = 'CV27-'.strtoupper(Str::random(6));
        } while (DB::table('inscricoes')->where('codigo', $codigo)->exists());

        return $codigo;
    }
};
