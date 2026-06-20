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
            ]
        );

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
