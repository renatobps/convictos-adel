<?php

namespace App\Filament\Resources\Membros;

use App\Filament\Resources\Membros\Pages\CreateMembro;
use App\Filament\Resources\Membros\Pages\EditMembro;
use App\Filament\Resources\Membros\Pages\ListMembros;
use App\Filament\Resources\Membros\Schemas\MembroForm;
use App\Filament\Resources\Membros\Tables\MembrosTable;
use App\Models\Membro;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MembroResource extends Resource
{
    protected static ?string $model = Membro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $modelLabel = 'Membro';

    protected static ?string $pluralModelLabel = 'Membros';

    protected static ?string $navigationLabel = 'Membros';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'ADEL';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return MembroForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembrosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembros::route('/'),
            'create' => CreateMembro::route('/create'),
            'edit' => EditMembro::route('/{record}/edit'),
        ];
    }
}
