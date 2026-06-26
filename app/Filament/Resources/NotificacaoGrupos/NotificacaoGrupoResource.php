<?php

namespace App\Filament\Resources\NotificacaoGrupos;

use App\Filament\Resources\NotificacaoGrupos\Pages\CreateNotificacaoGrupo;
use App\Filament\Resources\NotificacaoGrupos\Pages\EditNotificacaoGrupo;
use App\Filament\Resources\NotificacaoGrupos\Pages\ListNotificacaoGrupos;
use App\Filament\Resources\NotificacaoGrupos\Schemas\NotificacaoGrupoForm;
use App\Filament\Resources\NotificacaoGrupos\Tables\NotificacaoGruposTable;
use App\Models\NotificacaoGrupo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NotificacaoGrupoResource extends Resource
{
    protected static ?string $model = NotificacaoGrupo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static ?string $navigationLabel = 'Grupos';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Notificações';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return NotificacaoGrupoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificacaoGruposTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificacaoGrupos::route('/'),
            'create' => CreateNotificacaoGrupo::route('/create'),
            'edit' => EditNotificacaoGrupo::route('/{record}/edit'),
        ];
    }
}
