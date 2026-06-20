<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = ['P', 'M', 'G', 'GG'];

        $products = [
            [
                'name' => 'Camisa Azul Marinho',
                'category' => 'jersey',
                'description' => 'Modelo jogador com gola em V, detalhes em vermelho e número "UM" nas costas.',
                'price' => 89.90,
                'image' => 'assets/produtos/camisa-azul.jpg',
                'featured' => true,
            ],
            [
                'name' => 'Camisa Vinho',
                'category' => 'jersey',
                'description' => 'Modelo jogador com acabamento em dourado e número "UM" nas costas.',
                'price' => 89.90,
                'image' => 'assets/produtos/camisa-vinho.jpg',
            ],
            [
                'name' => 'Camisa Preta',
                'category' => 'jersey',
                'description' => 'Modelo jogador monocromático com detalhes em branco e número "UM".',
                'price' => 89.90,
                'image' => 'assets/produtos/camisa-preta.jpg',
            ],
            [
                'name' => 'Camisa Branca',
                'category' => 'jersey',
                'description' => 'Modelo jogador com detalhes em azul e vermelho e número "UM" nas costas.',
                'price' => 89.90,
                'image' => 'assets/produtos/camisa-branca.jpg',
            ],
            [
                'name' => 'Dry Fit Azul',
                'category' => 'dryfit',
                'description' => 'Tecido leve e esportivo com escudo "UM" no peito e arte gráfica nas costas.',
                'price' => 79.90,
                'image' => 'assets/produtos/dryfit-azul.jpg',
                'featured' => true,
            ],
            [
                'name' => 'Camiseta Branca',
                'category' => 'tee',
                'description' => 'Camiseta casual com logo na frente e escudo "UM" nas costas.',
                'price' => 59.90,
                'image' => 'assets/produtos/tee-branca.jpg',
            ],
            [
                'name' => 'Camiseta Preta',
                'category' => 'tee',
                'description' => 'Camiseta casual em preto com logo na frente e escudo "UM" nas costas.',
                'price' => 59.90,
                'image' => 'assets/produtos/tee-preta.jpg',
            ],
            [
                'name' => 'Camiseta Vinho "UM"',
                'category' => 'tee',
                'description' => 'Camiseta casual com estampa "UM" e "Para que todos sejam um" nas costas.',
                'price' => 59.90,
                'image' => 'assets/produtos/tee-vinho.jpg',
            ],
        ];

        foreach ($products as $index => $data) {
            Product::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($data['name'])],
                array_merge($data, [
                    'sizes' => $sizes,
                    'active' => true,
                    'hide_price' => false,
                    'available_for_sale' => false,
                    'sort_order' => $index,
                ])
            );
        }
    }
}
