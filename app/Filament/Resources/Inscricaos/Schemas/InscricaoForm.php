<?php

namespace App\Filament\Resources\Inscricaos\Schemas;

use App\Models\Inscricao;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class InscricaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ingresso digital')
                    ->visibleOn('edit')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('codigo')
                            ->label('Código do ingresso')
                            ->content(fn (?Inscricao $record): string => $record?->codigo ?? '—'),
                        Placeholder::make('ingresso_link')
                            ->label('Link público')
                            ->content(fn (?Inscricao $record): HtmlString => new HtmlString(
                                $record
                                    ? '<a href="'.e($record->urlIngresso()).'" target="_blank" style="color:#CF3136;font-weight:600;">Abrir ingresso digital</a>'
                                    : '—'
                            )),
                        Placeholder::make('qr')
                            ->label('QR Code')
                            ->columnSpanFull()
                            ->content(fn (?Inscricao $record): HtmlString => new HtmlString(
                                $record
                                    ? '<img src="'.$record->qrDataUri(200).'" alt="QR Code" style="width:180px;height:180px;border:1px solid #e5e7eb;border-radius:8px;">'
                                    : '—'
                            )),
                    ]),
                TextInput::make('nome')
                    ->label('Nome completo')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email(),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->required()
                    ->rules([
                        fn (?Inscricao $record): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                            if (Inscricao::jaExisteWhatsapp((string) $value, $record?->id)) {
                                $fail('Este número de celular já foi usado em outra inscrição.');
                            }
                        },
                    ]),
                TextInput::make('idade')
                    ->label('Idade')
                    ->numeric()
                    ->minValue(10)
                    ->maxValue(120),
                Select::make('tipo_ingresso')
                    ->label('Tipo de inscrição')
                    ->options(Inscricao::tipoIngressoOptions())
                    ->default(Inscricao::TIPO_COM_CAMISETA)
                    ->required()
                    ->live(),
                TextInput::make('valor')
                    ->label('Valor (R$)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                Select::make('tamanho_camiseta')
                    ->label('Tamanho da camiseta')
                    ->options(Inscricao::tamanhoCamisetaOptions())
                    ->required(fn (callable $get): bool => $get('tipo_ingresso') === Inscricao::TIPO_COM_CAMISETA)
                    ->visible(fn (callable $get): bool => $get('tipo_ingresso') !== Inscricao::TIPO_SEM_CAMISETA)
                    ->dehydrated(fn (callable $get): bool => $get('tipo_ingresso') === Inscricao::TIPO_COM_CAMISETA),
                Select::make('igreja_id')
                    ->label('Igreja')
                    ->relationship('igrejaRel', 'bairro')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state) {
                            $igreja = \App\Models\Igreja::find($state);
                            $set('igreja', $igreja?->nomeNoFormulario());
                        }
                    }),
                TextInput::make('igreja')
                    ->label('Igreja (texto)')
                    ->disabled()
                    ->dehydrated(),
                Toggle::make('lider_jovens')
                    ->label('Líder de jovens'),
                Select::make('status')
                    ->label('Status')
                    ->options(Inscricao::statusOptions())
                    ->default(Inscricao::STATUS_PENDENTE)
                    ->required(),
                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
