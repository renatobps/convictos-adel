<?php

namespace App\Filament\Resources\Igrejas\Schemas;

use App\Models\Membro;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class IgrejaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bairro')
                    ->label('Bairro / nome da igreja')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('regional_id')
                    ->label('Regional')
                    ->relationship(
                        'regional',
                        'nome',
                        fn ($query) => self::scopeRegionais($query)
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('dirigente_membro_id')
                    ->label('Dirigente')
                    ->options(fn () => Membro::query()->orderBy('nome')->pluck('nome', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state) {
                            $membro = Membro::find($state);
                            $set('dirigente', $membro?->nome);
                        }
                    }),
                TextInput::make('dirigente')
                    ->label('Nome do dirigente')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Regional>  $query
     */
    private static function scopeRegionais($query)
    {
        $user = Auth::user();
        if ($user === null || $user->isAdmin()) {
            return $query;
        }

        $ids = $user->regionalScopeIds();
        if (empty($ids)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $ids);
    }
}
