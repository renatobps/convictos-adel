<?php

namespace Database\Seeders;

use App\Models\Igreja;
use App\Models\Membro;
use App\Models\Regional;
use Illuminate\Database\Seeder;

class IgrejaSeeder extends Seeder
{
    /** @var array<string, string> */
    private array $aliasesDirigente = [
        'JAIME JESUS RODRIGUES' => 'JAIME DE JESUS RODRIGUES',
        'GASPAR BENARDES DE FREITAS' => 'GASPAR NARDES DE FREITAS',
        'NILTON PEREIRA DA SILVA' => 'HILTON PEREIRA DA SILVA',
    ];

    public function run(): void
    {
        $this->call(RegionalSeeder::class);

        $regionais = $this->mapRegionais();
        $membros = Membro::query()->pluck('id', 'nome');

        $igrejas = [
            ['bairro' => 'ALTO DAS CARAÍBAS', 'dirigente' => 'JOSÉ DA CRUZ VERDIANO', 'regional' => 2],
            ['bairro' => 'ALVORADA I', 'dirigente' => 'ANTÔNIO MARCOS DA SILVA', 'regional' => 2],
            ['bairro' => 'ALVORADA II', 'dirigente' => 'OSMAR RAMOS DOS SANTOS', 'regional' => 2],
            ['bairro' => 'AMERICANOS', 'dirigente' => 'VITALINO GOMES SANTIAGO', 'regional' => 2],
            ['bairro' => 'BRASILIA SUL', 'dirigente' => 'SERGIO JOSÉ DA SILVA', 'regional' => 3],
            ['bairro' => 'BURITIS', 'dirigente' => 'GILSON DE SOUZA E SILVA', 'regional' => 5],
            ['bairro' => 'CADEL', 'dirigente' => 'SEBASTIÃO TAVARES DA SILVA', 'regional' => 1],
            ['bairro' => 'CENTRO', 'dirigente' => 'WENDES RIBEIRO DOS SANTOS', 'regional' => 1],
            ['bairro' => 'COPAÍBAS', 'dirigente' => 'RENAN CARLOS BARBOSA DE OLIVEIRA', 'regional' => 3],
            ['bairro' => 'CRUZEIRO', 'dirigente' => 'FERNANDO DA SILVA BENTO', 'regional' => 5],
            ['bairro' => 'ESMERALDAS', 'dirigente' => 'JOÃO ALEXANDRE DOS SANTOS', 'regional' => 1],
            ['bairro' => 'FAZENDA RETIRO', 'dirigente' => 'JECONIAS JOSÉ BUENO', 'regional' => 5],
            ['bairro' => 'FRACAROLI', 'dirigente' => 'JAIME JESUS RODRIGUES', 'regional' => 4],
            ['bairro' => 'INDUSTRIAL', 'dirigente' => 'MOIZES ARAUJO CUNHA', 'regional' => 3],
            ['bairro' => 'INGÁ CENTRAL', 'dirigente' => 'JOSÉ AUGUSTO DE JESUS', 'regional' => 4],
            ['bairro' => 'JARDIM BANDEIRANTES', 'dirigente' => 'JOÃO VICENTE DA SILVA', 'regional' => 3],
            ['bairro' => 'JARDIM EDITH', 'dirigente' => 'ERINALDO SANTOS SENA', 'regional' => 2],
            ['bairro' => 'JARDIM LUZILIA', 'dirigente' => 'EDINALDO FERREIRA DA SILVA', 'regional' => 2],
            ['bairro' => 'JARDIM ZULEIKA', 'dirigente' => 'ANTÔNIO PRUDÊNCIO FILHO', 'regional' => 4],
            ['bairro' => 'JK I', 'dirigente' => 'NELSON RIBEIRO DA SILVA', 'regional' => 2],
            ['bairro' => 'JK II', 'dirigente' => 'CLÉZIO LOPES DA SILVA', 'regional' => 2],
            ['bairro' => 'LAJES', 'dirigente' => 'DOURIVAL BORGES DAS NEVES', 'regional' => 5],
            ['bairro' => 'LUZÍLIA PARQUE', 'dirigente' => 'CÍCERO LINDOMAR ALVES DE SOUZA', 'regional' => 3],
            ['bairro' => 'MANDU II', 'dirigente' => 'DANILO SOARES MEIRELES', 'regional' => 2],
            ['bairro' => 'MANIRATUBA', 'dirigente' => 'HEBER PEREIRA DOS SANTOS', 'regional' => 5],
            ['bairro' => 'MINGONE I', 'dirigente' => 'RENATO GOMES DA SILVA', 'regional' => 4],
            ['bairro' => 'MINGONE II', 'dirigente' => 'PAULO ROGÉRIO DE SOUZA', 'regional' => 4],
            ['bairro' => 'NORTE MARAVILHA', 'dirigente' => 'FRANCISCO CÉLIO PINTO', 'regional' => 1],
            ['bairro' => 'NORTE SERRINHA', 'dirigente' => 'ESAÚ DE OLIVEIRA', 'regional' => 2],
            ['bairro' => 'PARQUE DO CERRADO', 'dirigente' => 'NILTON PEREIRA DA SILVA', 'regional' => 1],
            ['bairro' => 'PARQUE ESTRELA DALVA I', 'dirigente' => 'FLÁVIO BARROSO DA SILVA', 'regional' => 1],
            ['bairro' => 'PARQUE ESTRELA DALVA II', 'dirigente' => 'GASPAR BENARDES DE FREITAS', 'regional' => 1],
            ['bairro' => 'PARQUE ESTRELA DALVA III', 'dirigente' => 'MARCO ANTÔNIO BARCELOS PINTO', 'regional' => 2],
            ['bairro' => 'PARQUE ESTRELA DALVA IV', 'dirigente' => 'JEAN BARROS DOS SANTOS', 'regional' => 1],
            ['bairro' => 'PARQUE ESTRELA DALVA IX', 'dirigente' => 'JONIVON BARBOSA DE JESUS', 'regional' => 4],
            ['bairro' => 'PARQUE ESTRELA DALVA V', 'dirigente' => 'RENATO DE OLIVEIRA PEREIRA', 'regional' => 1],
            ['bairro' => 'PRÓ LOTE', 'dirigente' => 'ISRAEL GOMES DA SILVA', 'regional' => 4],
            ['bairro' => 'ROSÁRIO', 'dirigente' => 'FERNANDO PEREIRA DE SOUZA', 'regional' => 1],
            ['bairro' => 'SALTO VERDE', 'dirigente' => 'VANINHO DE SOUSA BRAGA', 'regional' => 1],
            ['bairro' => 'SAMAMBAIA E SÃO BENTO', 'dirigente' => 'EURICO BRAZ DE QUEIROZ', 'regional' => 5],
            ['bairro' => 'SANTA FÉ I', 'dirigente' => 'CLEBER TAVARES DE ALMEIDA', 'regional' => 3],
            ['bairro' => 'SANTA FÉ II', 'dirigente' => 'GILSON BATISTA BARBOSA', 'regional' => 3],
            ['bairro' => 'SETOR LESTE', 'dirigente' => 'ANTÔNIO MARCOS S. SILVA', 'regional' => 1],
            ['bairro' => 'SETOR SUL', 'dirigente' => 'JOSÉ DA CRUZ VERDIANO', 'regional' => 1],
            ['bairro' => 'SETOR VIEGAS', 'dirigente' => 'CRISTIANO LOPES DE CAMARGO', 'regional' => 1],
            ['bairro' => 'SOL NASCENTE', 'dirigente' => 'ALEX DIVINO', 'regional' => 4],
            ['bairro' => 'SÃO SEBASTIÃO', 'dirigente' => 'RODRIGO BARBOSA DA SILVA', 'regional' => 1],
            ['bairro' => 'TRÊS PODERES', 'dirigente' => 'WELLINGTON SANTOS MONTEIRO', 'regional' => 3],
            ['bairro' => 'VALPARAÍSO', 'dirigente' => 'EDSON DA SILVA GOIS', 'regional' => 4],
            ['bairro' => 'VILA GUARÁ', 'dirigente' => 'JONAS NEVES ALECRIM', 'regional' => 3],
            ['bairro' => 'VILA JURACY', 'dirigente' => 'ÁLVARO FEITOSA DE AQUINO', 'regional' => 1],
            ['bairro' => 'VILA SÃO JOSÉ', 'dirigente' => 'JHÔNATAS TEOFILO DOS REIS', 'regional' => 1],
            ['bairro' => 'ÁGUA QUENTE', 'dirigente' => 'WILSON WALTER ALEXANDRE MULATO', 'regional' => 2],
        ];

        foreach ($igrejas as $dados) {
            $regionalId = $regionais[$dados['regional']] ?? null;

            if ($regionalId === null) {
                $this->command?->warn("Regional não encontrada: {$dados['regional']} ({$dados['bairro']})");

                continue;
            }

            $nomeDirigente = $this->aliasesDirigente[$dados['dirigente']] ?? $dados['dirigente'];
            $membroId = $membros[$nomeDirigente] ?? null;

            if ($membroId === null) {
                $membroId = Membro::query()
                    ->whereRaw('UPPER(nome) = ?', [mb_strtoupper($nomeDirigente)])
                    ->value('id');
            }

            if ($membroId === null) {
                $this->command?->warn("Dirigente não encontrado: {$dados['dirigente']} ({$dados['bairro']})");
            }

            Igreja::updateOrCreate(
                ['bairro' => $dados['bairro']],
                [
                    'regional_id' => $regionalId,
                    'dirigente' => $nomeDirigente,
                    'dirigente_membro_id' => $membroId,
                ],
            );
        }
    }

    /** @return array<int, int> */
    private function mapRegionais(): array
    {
        $map = [];

        foreach (Regional::query()->get(['id', 'nome']) as $regional) {
            if (preg_match('/(\d+)/', $regional->nome, $matches)) {
                $map[(int) $matches[1]] = $regional->id;
            }
        }

        return $map;
    }
}
