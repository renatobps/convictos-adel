<?php

namespace App\Filament\Resources\NotificacaoGrupos\Tables;

use App\Models\NotificacaoGrupo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificacaoGruposTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                TextColumn::make('nome')
                    ->label('Grupo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => NotificacaoGrupo::tipoOptions()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('descricao_destino')
                    ->label('Destino')
                    ->getStateUsing(fn (NotificacaoGrupo $record) => $record->descricaoDestino()),
                TextColumn::make('destinatarios')
                    ->label('Inscritos c/ WhatsApp')
                    ->getStateUsing(fn (NotificacaoGrupo $record) => $record->contarDestinatarios()),
                TextColumn::make('sistema')
                    ->label('Sistema')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Sim' : 'Não')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(NotificacaoGrupo::tipoOptions()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (NotificacaoGrupo $record) => ! $record->sistema),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
