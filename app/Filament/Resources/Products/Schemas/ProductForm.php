<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome do produto')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Gerado automaticamente a partir do nome.'),
                Select::make('category')
                    ->label('Categoria')
                    ->options(Product::CATEGORIES)
                    ->required()
                    ->default('tee'),
                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Preço')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('R$')
                    ->step('0.01'),
                TextInput::make('stock')
                    ->label('Estoque (deixe vazio para ilimitado)')
                    ->numeric(),
                Toggle::make('hide_price')
                    ->label('Ocultar preço (mostrar "Sob consulta")')
                    ->helperText('Quando ligado, o valor não aparece no site.'),
                Toggle::make('available_for_sale')
                    ->label('Disponível para compra')
                    ->helperText('Desligado = produto apenas para exibição (sem botão de compra).'),
                FileUpload::make('image')
                    ->label('Imagem')
                    ->image()
                    ->disk('public')
                    ->directory('produtos')
                    ->visibility('public')
                    ->imageEditor()
                    ->columnSpanFull(),
                TagsInput::make('sizes')
                    ->label('Tamanhos disponíveis')
                    ->placeholder('Ex: P, M, G, GG')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Ordem de exibição')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->label('Ativo (visível na loja)')
                    ->default(true),
                Toggle::make('featured')
                    ->label('Destaque'),
            ]);
    }
}
