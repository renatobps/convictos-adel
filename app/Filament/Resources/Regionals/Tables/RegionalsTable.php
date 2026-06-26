<?php

namespace App\Filament\Resources\Regionals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegionalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                TextColumn::make('nome')
                    ->label('Regional')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pastor_responsavel')
                    ->label('Pastor responsável')
                    ->searchable(),
                TextColumn::make('igrejas_count')
                    ->label('Igrejas')
                    ->counts('igrejas'),
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
