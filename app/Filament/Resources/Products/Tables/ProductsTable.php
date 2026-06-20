<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
                    ->description(fn ($record) => $record->hide_price ? 'oculto no site' : null)
                    ->sortable(),
                ToggleColumn::make('available_for_sale')
                    ->label('À venda'),
                TextColumn::make('stock')
                    ->label('Estoque')
                    ->numeric()
                    ->placeholder('Ilimitado')
                    ->sortable(),
                ToggleColumn::make('active')
                    ->label('Ativo'),
                ToggleColumn::make('featured')
                    ->label('Destaque'),
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
                TernaryFilter::make('available_for_sale')
                    ->label('Disponível para compra'),
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
