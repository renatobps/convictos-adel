<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscricaoConfigSeeder extends Seeder
{
    public function run(): void
    {
        if (! DB::table('inscricao_meta_configuracoes')->count()) {
            DB::table('inscricao_meta_configuracoes')->insert([
                'meta_total' => 500,
                'valor_inscricao' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
