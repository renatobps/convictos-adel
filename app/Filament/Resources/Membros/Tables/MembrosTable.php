<?php

namespace App\Filament\Resources\Membros\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembrosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('cargo.nome')
                    ->label('Cargo')
                    ->sortable(),
                TextColumn::make('telefone')
                    ->label('Telefone')
                    ->toggleable(),
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
