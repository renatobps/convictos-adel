<?php

namespace App\Filament\Resources\AtividadeLogs;

use App\Filament\Resources\AtividadeLogs\Pages\ListAtividadeLogs;
use App\Filament\Resources\AtividadeLogs\Tables\AtividadeLogsTable;
use App\Models\AtividadeLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AtividadeLogResource extends Resource
{
    protected static ?string $model = AtividadeLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'Log';

    protected static ?string $pluralModelLabel = 'Logs';

    protected static ?string $navigationLabel = 'Logs';

    protected static ?string $slug = 'adel/logs';

    protected static ?int $navigationSort = 91;

    protected static string|\UnitEnum|null $navigationGroup = 'ADEL';

    protected static ?string $navigationParentItem = 'Configurações';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AtividadeLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtividadeLogs::route('/'),
        ];
    }
}
