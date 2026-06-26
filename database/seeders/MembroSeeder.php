<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Membro;
use Illuminate\Database\Seeder;

class MembroSeeder extends Seeder
{
    private const SENHA_PADRAO = 'Adel2026@';

    public function run(): void
    {
        $cargos = Cargo::query()->pluck('id', 'nome');

        $membros = [
            ['nome' => 'ALEX DIVINO', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'ANTÔNIO MARCOS DA SILVA', 'cargo' => 'PASTOR'],
            ['nome' => 'ANTÔNIO MARCOS S. SILVA', 'cargo' => 'PASTOR'],
            ['nome' => 'ANTÔNIO PRUDÊNCIO FILHO', 'cargo' => 'PASTOR'],
            ['nome' => 'CLEBER TAVARES DE ALMEIDA', 'cargo' => 'PASTOR'],
            ['nome' => 'CLÉZIO LOPES DA SILVA', 'cargo' => 'PASTOR'],
            ['nome' => 'CRISTIANO LOPES DE CAMARGO', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'CÍCERO LINDOMAR ALVES DE SOUZA', 'cargo' => 'PASTOR'],
            ['nome' => 'DANILO SOARES MEIRELES', 'cargo' => 'PASTOR'],
            ['nome' => 'DOURIVAL BORGES DAS NEVES', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'EDINALDO FERREIRA DA SILVA', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'EDSON DA SILVA GOIS', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'ERINALDO SANTOS SENA', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'ESAÚ DE OLIVEIRA', 'cargo' => 'PASTOR'],
            ['nome' => 'EURICO BRAZ DE QUEIROZ', 'cargo' => 'PASTOR'],
            ['nome' => 'FERNANDO DA SILVA BENTO', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'FERNANDO PEREIRA DE SOUZA', 'cargo' => 'PASTOR'],
            ['nome' => 'FLÁVIO BARROSO DA SILVA', 'cargo' => 'PASTOR'],
            ['nome' => 'FRANCISCO CÉLIO PINTO', 'cargo' => 'PASTOR'],
            ['nome' => 'GASPAR NARDES DE FREITAS', 'cargo' => 'PASTOR'],
            ['nome' => 'GILSON BATISTA BARBOSA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'GILSON DE SOUZA E SILVA', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'HEBER PEREIRA DOS SANTOS', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'HILTON PEREIRA DA SILVA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'ISRAEL GOMES DA SILVA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'JAIME DE JESUS RODRIGUES', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'JEAN BARROS DOS SANTOS', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'JECONIAS JOSÉ BUENO', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'JHÔNATAS TEOFILO DOS REIS', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'JOAQUIM RIBEIRO DE LIMA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'JONAS NEVES ALECRIM', 'cargo' => 'PASTOR'],
            ['nome' => 'JONIVON BARBOSA DE JESUS', 'cargo' => 'PASTOR'],
            ['nome' => 'JOSÉ AUGUSTO DE JESUS', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'JOSÉ DA CRUZ VERDIANO', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'JOÃO ALEXANDRE DOS SANTOS', 'cargo' => 'PASTOR'],
            ['nome' => 'JOÃO VICENTE DA SILVA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'Juliana', 'email' => 'jhuliana2014@gmail.com', 'cargo' => 'Líder de Jovens Regional V', 'telefone' => '61999393708'],
            ['nome' => 'Lucas da Silva meireles', 'email' => 'lm1499168@gmail.com', 'cargo' => 'Líder de Jovens Regional I', 'telefone' => '61996578375'],
            ['nome' => 'MARCO ANTÔNIO BARCELOS PINTO', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'Marcos Gabriel Barcelos', 'email' => 'marcosbarcelos269@gmail.com', 'cargo' => 'Líder de Jovens Regional III', 'telefone' => '61993075995'],
            ['nome' => 'MOIZES ARAUJO CUNHA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'Naate Emine Costa Tavares Ribeiro', 'email' => 'naatemine@gmail.com', 'cargo' => 'Líder Juventude ADEL', 'telefone' => '61994053384'],
            ['nome' => 'NELSON RIBEIRO DA SILVA', 'cargo' => 'PASTOR'],
            ['nome' => 'OSMAR RAMOS DOS SANTOS', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'Patrícia Ferreira de Souza e silva', 'email' => 'ssfpatricia@hotmail.com', 'cargo' => 'Líder de Jovens Regional IV', 'telefone' => '61996998528'],
            ['nome' => 'PAULO ROGÉRIO DE SOUZA', 'cargo' => 'PASTOR'],
            ['nome' => 'Rafael Tavares', 'email' => 'rafaelttavares20@gmail.com', 'cargo' => 'Líder Juventude ADEL', 'telefone' => '61998518369'],
            ['nome' => 'RENAN CARLOS BARBOSA DE OLIVEIRA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'RENATO BENTO PEREIRA DE SOUZA', 'email' => 'renato.bps@hotmail.com', 'cargo' => 'Gestor', 'telefone' => '(61) 99859-5681'],
            ['nome' => 'RENATO DE OLIVEIRA PEREIRA', 'cargo' => 'EVANGELISTA'],
            ['nome' => 'RENATO GOMES DA SILVA', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'RODRIGO BARBOSA DA SILVA', 'cargo' => 'PASTOR'],
            ['nome' => 'SEBASTIÃO TAVARES DA SILVA', 'cargo' => 'PASTOR'],
            ['nome' => 'SERGIO JOSÉ DA SILVA', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'VANINHO DE SOUSA BRAGA', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'VITALINO GOMES SANTIAGO', 'cargo' => 'PRESBÍTERO'],
            ['nome' => 'WELLINGTON SANTOS MONTEIRO', 'cargo' => 'PASTOR'],
            ['nome' => 'WENDES RIBEIRO DOS SANTOS', 'cargo' => 'PASTOR'],
            ['nome' => 'WILSON WALTER ALEXANDRE MULATO', 'cargo' => 'PASTOR'],
            ['nome' => 'ÁLVARO FEITOSA DE AQUINO', 'cargo' => 'PRESBÍTERO'],
        ];

        foreach ($membros as $dados) {
            $cargoId = $cargos[$dados['cargo']] ?? null;

            if ($cargoId === null) {
                $this->command?->warn("Cargo não encontrado: {$dados['cargo']} ({$dados['nome']})");

                continue;
            }

            $atributos = [
                'nome' => $dados['nome'],
                'cargo_id' => $cargoId,
                'telefone' => $dados['telefone'] ?? null,
                'senha' => self::SENHA_PADRAO,
            ];

            if (! empty($dados['email'])) {
                Membro::updateOrCreate(
                    ['email' => $dados['email']],
                    $atributos,
                );
            } else {
                Membro::updateOrCreate(
                    ['nome' => $dados['nome']],
                    array_merge($atributos, ['email' => null]),
                );
            }
        }
    }
}
