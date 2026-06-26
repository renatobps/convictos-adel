<?php

namespace Database\Seeders;

use App\Models\Igreja;
use App\Models\Membro;
use App\Models\Regional;
use Illuminate\Database\Seeder;

class RegionalSeeder extends Seeder
{
    public function run(): void
    {
        $pastores = [
            1 => 'Lucas da Silva meireles',
            2 => 'Rafael Tavares',
            3 => 'Marcos Gabriel Barcelos',
            4 => 'Patrícia Ferreira de Souza e silva',
            5 => 'Juliana',
        ];

        foreach ($pastores as $numero => $pastorNome) {
            Regional::firstOrCreate(
                ['nome' => 'REGIONAL '.$numero],
                ['pastor_responsavel' => $pastorNome],
            );
        }
    }
}
