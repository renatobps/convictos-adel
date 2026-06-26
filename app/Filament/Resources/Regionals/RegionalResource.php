<?php

namespace App\Filament\Resources\Regionals;

use App\Filament\Resources\Regionals\Pages\CreateRegional;
use App\Filament\Resources\Regionals\Pages\EditRegional;
use App\Filament\Resources\Regionals\Pages\ListRegionals;
use App\Filament\Resources\Regionals\Schemas\RegionalForm;
use App\Filament\Resources\Regionals\Tables\RegionalsTable;
use App\Models\Regional;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RegionalResource extends Resource
{
    protected static ?string $model = Regional::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $modelLabel = 'Regional';

    protected static ?string $pluralModelLabel = 'Regionais';

    protected static ?string $navigationLabel = 'Regionais';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'ADEL';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return RegionalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegionalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegionals::route('/'),
            'create' => CreateRegional::route('/create'),
            'edit' => EditRegional::route('/{record}/edit'),
        ];
    }
}
