<?php

namespace App\Filament\Resources\Igrejas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IgrejasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('bairro')
            ->columns([
                TextColumn::make('bairro')
                    ->label('Igreja')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('regional.nome')
                    ->label('Regional')
                    ->sortable(),
                TextColumn::make('dirigente')
                    ->label('Dirigente')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('regional_id')
                    ->label('Regional')
                    ->relationship('regional', 'nome'),
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
