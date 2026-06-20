<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('Imagem')
                    ->getStateUsing(fn ($record) => $record->image_url)
                    ->square(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Categoria')
                    ->formatStateUsing(fn ($state) => Product::CATEGORIES[$state] ?? $state)
                    ->badge(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Estoque')
                    ->numeric()
                    ->placeholder('Ilimitado')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
                IconColumn::make('featured')
                    ->label('Destaque')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(Product::CATEGORIES),
                TernaryFilter::make('active')
                    ->label('Ativo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
