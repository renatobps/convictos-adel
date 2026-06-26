<?php

namespace Database\Seeders;

use App\Models\Enquete;
use App\Models\Igreja;
use App\Models\Inscricao;
use App\Models\NotificacaoGrupo;
use App\Models\Regional;
use Illuminate\Database\Seeder;

class NotificacaoGrupoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Igreja::query()->with('regional')->orderBy('bairro')->get() as $igreja) {
            NotificacaoGrupo::updateOrCreate(
                [
                    'tipo' => NotificacaoGrupo::TIPO_IGREJA,
                    'igreja_id' => $igreja->id,
                ],
                [
                    'nome' => 'Igreja: '.$igreja->bairro,
                    'regional_id' => null,
                    'status_inscricao' => null,
                    'sistema' => true,
                ],
            );
        }

        foreach (Regional::query()->orderBy('nome')->get() as $regional) {
            NotificacaoGrupo::updateOrCreate(
                [
                    'tipo' => NotificacaoGrupo::TIPO_REGIONAL,
                    'regional_id' => $regional->id,
                ],
                [
                    'nome' => 'Regional: '.$regional->nome,
                    'igreja_id' => null,
                    'status_inscricao' => null,
                    'sistema' => true,
                ],
            );
        }

        NotificacaoGrupo::updateOrCreate(
            [
                'tipo' => NotificacaoGrupo::TIPO_INSCRITOS,
                'status_inscricao' => null,
                'igreja_id' => null,
                'regional_id' => null,
            ],
            [
                'nome' => 'Inscritos: Todos',
                'sistema' => true,
            ],
        );

        foreach (Inscricao::statusOptions() as $status => $label) {
            NotificacaoGrupo::updateOrCreate(
                [
                    'tipo' => NotificacaoGrupo::TIPO_INSCRITOS,
                    'status_inscricao' => $status,
                    'igreja_id' => null,
                    'regional_id' => null,
                ],
                [
                    'nome' => 'Inscritos: '.$label,
                    'sistema' => true,
                ],
            );
        }
    }
}
