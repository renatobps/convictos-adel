<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@convictos.com.br'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('convictos2027'),
                'is_admin' => true,
            ]
        );

        $this->call([
            ProductSeeder::class,
            InscricaoConfigSeeder::class,
            CargoSeeder::class,
            MembroSeeder::class,
            RegionalSeeder::class,
            IgrejaSeeder::class,
            NotificacaoGrupoSeeder::class,
        ]);
    }
}
