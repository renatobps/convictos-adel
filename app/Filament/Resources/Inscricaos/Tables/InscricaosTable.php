<?php

namespace App\Filament\Resources\Inscricaos\Tables;

use App\Models\Igreja;
use App\Models\Inscricao;
use App\Models\Regional;
use App\Services\NotificacaoService;
use App\Services\WhatsAppService;
use App\Support\EmailConfig;
use App\Support\FilamentUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InscricaosTable
{
    public static function configure(Table $table): Table
    {
        $cell = ['class' => 'py-2 px-3 text-sm'];

        return $table
            ->defaultSort('created_at', 'desc')
            ->recordAction('detalhes')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Código copiado')
                    ->searchable()
                    ->extraCellAttributes($cell),
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->extraCellAttributes($cell),
                TextColumn::make('tamanho_camiseta')
                    ->label('Camiseta')
                    ->badge()
                    ->extraCellAttributes($cell),
                IconColumn::make('camiseta_retirada')
                    ->label('Retirada')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::XCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->extraCellAttributes($cell),
                TextColumn::make('igreja')
                    ->label('Igreja / Regional')
                    ->html()
                    ->state(fn (Inscricao $record): string => self::igrejaRegionalBadge($record))
                    ->searchable()
                    ->extraCellAttributes($cell),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Inscricao::statusOptions()[$state] ?? ucfirst((string) $state))
                    ->color(fn (string $state): string => match ($state) {
                        Inscricao::STATUS_AGUARDANDO => 'warning',
                        Inscricao::STATUS_CONFIRMADA => 'success',
                        Inscricao::STATUS_CANCELADA => 'danger',
                        default => 'gray',
                    })
                    ->extraCellAttributes($cell),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Inscricao::statusOptions()),
                SelectFilter::make('regional')
                    ->label('Regional')
                    ->searchable()
                    ->options(fn (): array => Regional::query()
                        ->orderBy('nome')
                        ->pluck('nome', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value']),
                        fn (Builder $q): Builder => $q->whereHas(
                            'igrejaRel',
                            fn (Builder $sub): Builder => $sub->where('regional_id', $data['value'])
                        )
                    )),
                SelectFilter::make('igreja_id')
                    ->label('Igreja')
                    ->searchable()
                    ->options(fn (): array => Igreja::query()
                        ->with('regional')
                        ->orderBy('bairro')
                        ->get()
                        ->mapWithKeys(fn (Igreja $igreja): array => [
                            $igreja->id => $igreja->bairro
                                .($igreja->regional ? ' ('.$igreja->regional->abreviacao().')' : ''),
                        ])
                        ->all()),
            ])
            ->headerActions([
                self::exportarExcelAction(),
                self::exportarPdfAction(),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->tooltip('Editar'),
                ActionGroup::make([
                    self::detalhesAction(),
                    self::verIngressoAction(),
                    self::comprovantePdfAction(),
                    self::enviarWhatsAppAction(),
                    self::enviarEmailAction(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::marcarRetiradaLoteAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function marcarRetiradaLoteAction(): BulkAction
    {
        return BulkAction::make('marcarRetiradaLote')
            ->label('Marcar camiseta como retirada')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->modalHeading('Marcar camisetas como retiradas')
            ->modalDescription('Informe quem está retirando as camisetas selecionadas.')
            ->modalWidth(Width::Medium)
            ->schema([
                TextInput::make('retirado_por')
                    ->label('Retirado por')
                    ->placeholder('Nome de quem retirou as camisetas')
                    ->required()
                    ->maxLength(255),
            ])
            ->action(function (Collection $records, array $data): void {
                $nome = trim($data['retirado_por']);
                $agora = now();

                $records->each(fn (Inscricao $record) => $record->update([
                    'camiseta_retirada' => true,
                    'camiseta_retirada_em' => $agora,
                    'camiseta_retirada_por' => $nome,
                ]));

                Notification::make()
                    ->title($records->count().' camiseta(s) marcada(s) como retirada por '.$nome.'.')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Nome da igreja sem o sufixo da regional.
     */
    private static function nomeIgreja(Inscricao $record): string
    {
        if ($record->igrejaRel?->bairro) {
            return $record->igrejaRel->bairro;
        }

        // Registros antigos: remove " (REGIONAL X)" do texto salvo.
        return trim(preg_replace('/\s*\(.*\)\s*$/', '', (string) $record->igreja));
    }

    /**
     * Nome da igreja com a regional abreviada como badge inline.
     */
    private static function igrejaRegionalBadge(Inscricao $record): string
    {
        $igreja = e(self::nomeIgreja($record));
        $regional = $record->igrejaRel?->regional?->abreviacao();

        if (! $regional) {
            return $igreja;
        }

        $badge = '<span style="display:inline-block;margin-left:6px;padding:1px 7px;border-radius:9999px;'
            .'background:#dbeafe;color:#1e40af;font-size:0.7rem;font-weight:600;line-height:1.4;">'
            .e($regional).'</span>';

        return $igreja.$badge;
    }

    private static function detalhesAction(): Action
    {
        return Action::make('detalhes')
            ->label('Ver detalhes')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalHeading(fn (Inscricao $record): string => $record->nome)
            ->modalIcon(Heroicon::OutlinedIdentification)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Inscricao $record) => view('filament.inscricoes.detalhes', [
                'record' => $record,
                'igreja' => self::nomeIgreja($record),
                'regional' => $record->igrejaRel?->regional?->nome,
                'statusLabel' => Inscricao::statusOptions()[$record->status] ?? (string) $record->status,
            ]))
            ->extraModalFooterActions([
                self::alterarStatusAction(),
                self::marcarRetiradaAction(),
                self::desmarcarRetiradaAction(),
                self::verIngressoAction(),
                self::comprovantePdfAction(),
            ]);
    }

    private static function alterarStatusAction(): Action
    {
        return Action::make('alterarStatus')
            ->label('Alterar status')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('primary')
            ->modalHeading('Alterar status da inscrição')
            ->modalWidth(Width::Medium)
            ->fillForm(fn (Inscricao $record): array => ['status' => $record->status])
            ->schema([
                Select::make('status')
                    ->label('Status')
                    ->options(Inscricao::statusOptions())
                    ->required()
                    ->native(false),
            ])
            ->action(function (Inscricao $record, array $data): void {
                $record->update(['status' => $data['status']]);

                Notification::make()
                    ->title('Status atualizado para '.(Inscricao::statusOptions()[$data['status']] ?? $data['status']).'.')
                    ->success()
                    ->send();
            });
    }

    private static function marcarRetiradaAction(): Action
    {
        return Action::make('marcarRetirada')
            ->label('Marcar camiseta como retirada')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Inscricao $record): bool => ! $record->camiseta_retirada)
            ->modalHeading('Confirmar retirada da camiseta')
            ->modalWidth(Width::Medium)
            ->schema([
                TextInput::make('retirado_por')
                    ->label('Retirado por')
                    ->placeholder('Nome de quem retirou a camiseta')
                    ->required()
                    ->maxLength(255),
            ])
            ->action(function (Inscricao $record, array $data): void {
                $record->update([
                    'camiseta_retirada' => true,
                    'camiseta_retirada_em' => now(),
                    'camiseta_retirada_por' => trim($data['retirado_por']),
                ]);

                Notification::make()
                    ->title('Camiseta retirada por '.$record->camiseta_retirada_por.' em '.$record->camiseta_retirada_em->format('d/m/Y H:i').'.')
                    ->success()
                    ->send();
            });
    }

    private static function desmarcarRetiradaAction(): Action
    {
        return Action::make('desmarcarRetirada')
            ->label('Desmarcar retirada da camiseta')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('gray')
            ->visible(fn (Inscricao $record): bool => (bool) $record->camiseta_retirada)
            ->requiresConfirmation()
            ->action(function (Inscricao $record): void {
                $record->update([
                    'camiseta_retirada' => false,
                    'camiseta_retirada_em' => null,
                    'camiseta_retirada_por' => null,
                ]);

                Notification::make()
                    ->title('Retirada da camiseta desmarcada.')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<int, string>
     */
    private static function colunasExport(): array
    {
        return [
            'Código', 'Nome', 'WhatsApp', 'E-mail', 'Idade', 'Camiseta',
            'Camiseta retirada', 'Igreja', 'Regional', 'Líder', 'Status', 'Data',
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function linhaExport(Inscricao $record): array
    {
        return [
            (string) $record->codigo,
            (string) $record->nome,
            (string) $record->whatsapp,
            (string) $record->email,
            (string) $record->idade,
            (string) $record->tamanho_camiseta,
            $record->camiseta_retirada ? 'Sim' : 'Não',
            self::nomeIgreja($record),
            $record->igrejaRel?->regional?->abreviacao() ?? '—',
            $record->lider_jovens ? 'Sim' : 'Não',
            Inscricao::statusOptions()[$record->status] ?? (string) $record->status,
            $record->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Inscricao>
     */
    private static function registrosExport($livewire)
    {
        return $livewire->getFilteredSortedTableQuery()
            ->with('igrejaRel.regional')
            ->get();
    }

    private static function exportarExcelAction(): Action
    {
        return Action::make('exportarExcel')
            ->label('Exportar Excel')
            ->icon(Heroicon::OutlinedTableCells)
            ->color('success')
            ->action(function ($livewire): StreamedResponse {
                $registros = self::registrosExport($livewire);

                return response()->streamDownload(function () use ($registros): void {
                    $saida = fopen('php://output', 'w');
                    // BOM para o Excel reconhecer UTF-8 corretamente.
                    fwrite($saida, "\xEF\xBB\xBF");
                    fputcsv($saida, self::colunasExport(), ';');

                    foreach ($registros as $registro) {
                        fputcsv($saida, self::linhaExport($registro), ';');
                    }

                    fclose($saida);
                }, 'inscricoes-'.now()->format('Y-m-d_His').'.csv', [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                ]);
            });
    }

    private static function exportarPdfAction(): Action
    {
        return Action::make('exportarPdf')
            ->label('Exportar PDF')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->color('danger')
            ->action(function ($livewire): StreamedResponse {
                $registros = self::registrosExport($livewire);

                $pdf = Pdf::loadView('pdf.inscricoes', [
                    'registros' => $registros,
                    'colunas' => self::colunasExport(),
                    'linhas' => $registros->map(fn (Inscricao $r): array => self::linhaExport($r)),
                ])->setPaper('a4', 'landscape');

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    'inscricoes-'.now()->format('Y-m-d_His').'.pdf',
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    private static function verIngressoAction(): Action
    {
        return Action::make('verIngresso')
            ->label('Ingresso')
            ->icon(Heroicon::OutlinedQrCode)
            ->iconButton()
            ->color('gray')
            ->tooltip('Ver ingresso digital (QR Code)')
            ->url(fn (Inscricao $record): string => $record->urlIngresso())
            ->openUrlInNewTab();
    }

    private static function comprovantePdfAction(): Action
    {
        return Action::make('comprovantePdf')
            ->label('Comprovante PDF')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->iconButton()
            ->color('danger')
            ->tooltip('Baixar comprovante em PDF')
            ->url(fn (Inscricao $record): string => route('ingresso.pdf', ['inscricao' => $record->codigo]))
            ->openUrlInNewTab();
    }

    private static function enviarWhatsAppAction(): Action
    {
        return Action::make('enviarWhatsApp')
            ->label('WhatsApp')
            ->icon(fn () => view('filament.icons.whatsapp'))
            ->iconButton()
            ->color('success')
            ->tooltip('Enviar mensagem no WhatsApp')
            ->visible(fn (Inscricao $record): bool => filled($record->whatsapp))
            ->modalHeading(fn (Inscricao $record): string => 'Enviar WhatsApp — '.$record->nome)
            ->modalDescription('Envie texto ou imagem diretamente para o inscrito.')
            ->modalSubmitActionLabel('Enviar mensagem')
            ->extraModalFooterActions([
                Action::make('reenviarComprovanteWpp')
                    ->label('Enviar comprovante')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('gray')
                    ->visible(fn (Inscricao $record): bool => filled($record->codigo) && filled($record->whatsapp))
                    ->action(function (Inscricao $record): void {
                        $resultado = app(WhatsAppService::class)->reenviarComprovante($record);

                        if ($resultado['ok']) {
                            Notification::make()
                                ->title('Comprovante enviado no WhatsApp de '.$record->nome.'.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title($resultado['erro'] ?? 'Falha ao enviar comprovante.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->schema([
                Placeholder::make('destinatario')
                    ->label('Destinatário')
                    ->content(fn (Inscricao $record): string => (string) $record->whatsapp),
                Textarea::make('mensagem')
                    ->label('Mensagem')
                    ->rows(5)
                    ->placeholder('Placeholders: {nome_do_inscrito}, {tamanho_camiseta}')
                    ->required(fn (Get $get): bool => blank($get('arquivo'))),
                FileUpload::make('arquivo')
                    ->label('Imagem ou anexo (opcional)')
                    ->disk('public')
                    ->directory('notificacoes/midias')
                    ->maxSize(20480)
                    ->acceptedFileTypes([
                        'image/*',
                        'video/*',
                        'audio/*',
                        'application/pdf',
                    ])
                    ->required(fn (Get $get): bool => blank($get('mensagem'))),
            ])
            ->action(function (Inscricao $record, array $data): void {
                if ($msg = app(WhatsAppService::class)->obterMensagemSeDesconectado()) {
                    Notification::make()->title($msg)->danger()->send();

                    return;
                }

                $mensagem = trim($data['mensagem'] ?? '');
                $arquivo = FilamentUpload::resolve($data['arquivo'] ?? null);

                if ($arquivo === null && filled($data['arquivo'] ?? null)) {
                    Notification::make()
                        ->title('Aguarde o upload do anexo terminar e tente novamente.')
                        ->danger()
                        ->send();

                    return;
                }

                if ($mensagem === '' && $arquivo === null) {
                    Notification::make()
                        ->title('Informe a mensagem ou anexe um arquivo.')
                        ->danger()
                        ->send();

                    return;
                }

                $ok = app(NotificacaoService::class)->enviarParaInscricao(
                    $record,
                    $mensagem,
                    $arquivo,
                    'inscricao',
                );

                if ($ok) {
                    Notification::make()
                        ->title('Mensagem enviada para '.$record->nome.'.')
                        ->success()
                        ->send();

                    return;
                }

                $erro = app(WhatsAppService::class)->obterUltimoErro()
                    ?: 'Falha ao enviar mensagem.';

                Notification::make()->title($erro)->danger()->send();
            });
    }

    private static function enviarEmailAction(): Action
    {
        return Action::make('enviarEmail')
            ->label('E-mail')
            ->icon(Heroicon::OutlinedEnvelope)
            ->iconButton()
            ->color('info')
            ->tooltip('Enviar e-mail padrão')
            ->visible(fn (Inscricao $record): bool => EmailConfig::emailValidoParaEnvio($record->email))
            ->modalHeading(fn (Inscricao $record): string => 'Enviar e-mail — '.$record->nome)
            ->modalDescription('Selecione um dos e-mails padrão cadastrados em Notificações → Email.')
            ->modalSubmitActionLabel('Enviar e-mail')
            ->extraModalFooterActions([
                Action::make('reenviarComprovanteEmail')
                    ->label('Enviar comprovante')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('gray')
                    ->visible(fn (Inscricao $record): bool => filled($record->codigo) && EmailConfig::emailValidoParaEnvio($record->email))
                    ->action(function (Inscricao $record): void {
                        $resultado = EmailConfig::enviarComprovante($record);

                        if ($resultado['ok']) {
                            Notification::make()
                                ->title('Comprovante enviado para o e-mail de '.$record->nome.'.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title($resultado['mensagem'] ?? 'Falha ao enviar comprovante.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->schema([
                Placeholder::make('destinatario')
                    ->label('Destinatário')
                    ->content(fn (Inscricao $record): string => (string) $record->email),
                Select::make('tipo')
                    ->label('E-mail padrão')
                    ->options(fn (): array => EmailConfig::templateOptionsAtivos())
                    ->required()
                    ->live()
                    ->placeholder('Selecione…')
                    ->helperText(fn (): ?string => EmailConfig::templateOptionsAtivos() === []
                        ? 'Nenhum template ativo. Ative em Notificações → Email.'
                        : null),
                Placeholder::make('assunto')
                    ->label('Assunto')
                    ->content(function (Get $get, Inscricao $record): string {
                        $tipo = (string) ($get('tipo') ?? '');
                        if ($tipo === '') {
                            return '—';
                        }

                        return EmailConfig::substituirPlaceholders(
                            (string) EmailConfig::template($tipo)['assunto'],
                            $record,
                        );
                    })
                    ->visible(fn (Get $get): bool => filled($get('tipo'))),
            ])
            ->action(function (Inscricao $record, array $data): void {
                if (EmailConfig::templateOptionsAtivos() === []) {
                    Notification::make()
                        ->title('Nenhum template de e-mail ativo. Configure em Notificações → Email.')
                        ->danger()
                        ->send();

                    return;
                }

                $resultado = EmailConfig::enviarParaInscricao($record, (string) ($data['tipo'] ?? ''));

                if ($resultado['ok']) {
                    Notification::make()
                        ->title('E-mail enviado para '.$record->nome.'.')
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title($resultado['mensagem'] ?? 'Falha ao enviar e-mail.')
                    ->danger()
                    ->send();
            });
    }
}
