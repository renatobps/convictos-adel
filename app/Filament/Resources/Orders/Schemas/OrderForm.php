<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do cliente')
                    ->columns(2)
                    ->schema([
                        TextInput::make('reference')
                            ->label('Referência')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('customer_name')
                            ->label('Nome do cliente')
                            ->required(),
                        TextInput::make('customer_email')
                            ->label('E-mail')
                            ->email()
                            ->required(),
                        TextInput::make('customer_phone')
                            ->label('Telefone / WhatsApp')
                            ->tel(),
                    ]),
                Section::make('Pagamento e status')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status do pedido')
                            ->options(Order::STATUSES)
                            ->default('pendente')
                            ->required(),
                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('R$')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Select::make('payment_method')
                            ->label('Forma de pagamento')
                            ->options([
                                'mercadopago' => 'MercadoPago',
                                'pix' => 'PIX',
                                'manual' => 'Manual / Combinar',
                            ]),
                        TextInput::make('payment_status')
                            ->label('Status do pagamento'),
                        TextInput::make('payment_id')
                            ->label('ID do pagamento (MercadoPago)')
                            ->columnSpanFull(),
                    ]),
                Section::make('Itens do pedido')
                    ->schema([
                        Repeater::make('items')
                            ->label('Itens')
                            ->relationship()
                            ->columns(4)
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('Produto')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('size')
                                    ->label('Tamanho'),
                                TextInput::make('quantity')
                                    ->label('Qtd')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('Preço unit.')
                                    ->prefix('R$')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('R$')
                                    ->numeric(),
                            ]),
                    ]),
                Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ]);
    }
}
