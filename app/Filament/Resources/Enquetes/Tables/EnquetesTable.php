<?php

namespace App\Filament\Resources\Enquetes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnquetesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grupo.nome')
                    ->label('Grupo')
                    ->searchable(),
                TextColumn::make('envios_count')
                    ->label('Envios')
                    ->counts('envios'),
                TextColumn::make('respostas_count')
                    ->label('Respostas')
                    ->counts('respostas'),
                IconColumn::make('ativa')
                    ->label('Ativa')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
