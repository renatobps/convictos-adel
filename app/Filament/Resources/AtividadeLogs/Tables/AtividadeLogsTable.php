<?php

namespace App\Filament\Resources\AtividadeLogs\Tables;

use App\Models\AtividadeLog;
use App\Services\AtividadeLogService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AtividadeLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data e hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('usuario_nome')
                    ->label('Usuário')
                    ->description(fn (AtividadeLog $record): ?string => $record->usuario_email)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descricao')
                    ->label('Ação realizada')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('acao')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        AtividadeLogService::ACAO_CRIADO => 'Criação',
                        AtividadeLogService::ACAO_ATUALIZADO => 'Atualização',
                        AtividadeLogService::ACAO_EXCLUIDO => 'Exclusão',
                        AtividadeLogService::ACAO_LOGIN => 'Login',
                        AtividadeLogService::ACAO_CONFIG => 'Configuração',
                        AtividadeLogService::ACAO_NOTIFICACAO => 'Notificação',
                        default => $state ? ucfirst($state) : '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        AtividadeLogService::ACAO_CRIADO => 'success',
                        AtividadeLogService::ACAO_ATUALIZADO => 'info',
                        AtividadeLogService::ACAO_EXCLUIDO => 'danger',
                        AtividadeLogService::ACAO_LOGIN => 'gray',
                        AtividadeLogService::ACAO_CONFIG => 'warning',
                        AtividadeLogService::ACAO_NOTIFICACAO => 'primary',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('acao')
                    ->label('Tipo')
                    ->options([
                        AtividadeLogService::ACAO_CRIADO => 'Criação',
                        AtividadeLogService::ACAO_ATUALIZADO => 'Atualização',
                        AtividadeLogService::ACAO_EXCLUIDO => 'Exclusão',
                        AtividadeLogService::ACAO_LOGIN => 'Login',
                        AtividadeLogService::ACAO_CONFIG => 'Configuração',
                        AtividadeLogService::ACAO_NOTIFICACAO => 'Notificação',
                    ]),
            ])
            ->paginated([25, 50, 100])
            ->poll('30s');
    }
}
