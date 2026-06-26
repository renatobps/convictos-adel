<?php

namespace App\Filament\Resources\Enquetes;

use App\Filament\Resources\Enquetes\Pages\CreateEnquete;
use App\Filament\Resources\Enquetes\Pages\EditEnquete;
use App\Filament\Resources\Enquetes\Pages\ListEnquetes;
use App\Filament\Resources\Enquetes\Pages\ViewEnquete;
use App\Filament\Resources\Enquetes\Schemas\EnqueteForm;
use App\Filament\Resources\Enquetes\Tables\EnquetesTable;
use App\Models\Enquete;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EnqueteResource extends Resource
{
    protected static ?string $model = Enquete::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'Enquete';

    protected static ?string $pluralModelLabel = 'Enquetes';

    protected static ?string $navigationLabel = 'Enquetes';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'Notificações';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return EnqueteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnquetesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquetes::route('/'),
            'create' => CreateEnquete::route('/create'),
            'view' => ViewEnquete::route('/{record}'),
            'edit' => EditEnquete::route('/{record}/edit'),
        ];
    }
}
