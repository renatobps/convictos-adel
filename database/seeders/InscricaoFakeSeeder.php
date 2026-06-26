<?php

namespace Database\Seeders;

use App\Models\Igreja;
use App\Models\Inscricao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InscricaoFakeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('pt_BR');

        $igrejas = Igreja::query()->with('regional')->get();

        if ($igrejas->isEmpty()) {
            $this->call(IgrejaSeeder::class);
            $igrejas = Igreja::query()->with('regional')->get();
        }

        if ($igrejas->isEmpty()) {
            $this->command?->warn('Nenhuma igreja cadastrada. Abortando geração de inscrições fake.');

            return;
        }

        $tamanhos = array_keys(Inscricao::tamanhoCamisetaOptions());
        $status = array_keys(Inscricao::statusOptions());
        $total = 100;

        for ($i = 0; $i < $total; $i++) {
            // Percorre todas as igrejas em sequência para garantir cobertura
            // de todas as regionais e igrejas.
            $igreja = $igrejas[$i % $igrejas->count()];

            $nome = mb_strtoupper($faker->name());
            $statusSorteado = $faker->randomElement($status);
            $retirada = $faker->boolean(30);

            Inscricao::create([
                'nome' => $nome,
                'email' => Str::slug($nome, '.').'.'.$faker->numberBetween(1, 9999).'@fake.convictos',
                'whatsapp' => sprintf('(%02d) 9%04d-%04d', $faker->numberBetween(61, 99), $faker->numberBetween(0, 9999), $faker->numberBetween(0, 9999)),
                'idade' => (string) $faker->numberBetween(12, 45),
                'tamanho_camiseta' => $faker->randomElement($tamanhos),
                'camiseta_retirada' => $retirada,
                'camiseta_retirada_em' => $retirada ? $faker->dateTimeBetween('-10 days', 'now') : null,
                'camiseta_retirada_por' => $retirada ? mb_strtoupper($faker->name()) : null,
                'igreja_id' => $igreja->id,
                'igreja' => $igreja->nomeNoFormulario(),
                'lider_jovens' => $faker->boolean(20),
                'status' => $statusSorteado,
                'created_at' => $faker->dateTimeBetween('-30 days', 'now'),
                'updated_at' => now(),
            ]);
        }

        $this->command?->info("{$total} inscrições fake criadas em {$igrejas->count()} igrejas.");
    }
}
