<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        $cargos = [
            'EVANGELISTA',
            'Gestor',
            'Líder de Jovens Regional II',
            'Líder de Jovens Regional I',
            'Líder de Jovens Regional III',
            'Líder de Jovens Regional IV',
            'Líder de Jovens Regional V',
            'Líder Juventude ADEL',
            'PASTOR',
            'PRESBÍTERO',
        ];

        foreach ($cargos as $nome) {
            Cargo::updateOrCreate(['nome' => $nome]);
        }
    }
}
