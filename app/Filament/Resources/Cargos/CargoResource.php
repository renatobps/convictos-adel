<?php

namespace App\Filament\Resources\Cargos;

use App\Filament\Resources\Cargos\Pages\CreateCargo;
use App\Filament\Resources\Cargos\Pages\EditCargo;
use App\Filament\Resources\Cargos\Pages\ListCargos;
use App\Filament\Resources\Cargos\Schemas\CargoForm;
use App\Filament\Resources\Cargos\Tables\CargosTable;
use App\Models\Cargo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CargoResource extends Resource
{
    protected static ?string $model = Cargo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $modelLabel = 'Cargo';

    protected static ?string $pluralModelLabel = 'Cargos';

    protected static ?string $navigationLabel = 'Cargos';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'ADEL';

    protected static ?string $navigationParentItem = 'Membros';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CargoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CargosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCargos::route('/'),
            'create' => CreateCargo::route('/create'),
            'edit' => EditCargo::route('/{record}/edit'),
        ];
    }
}
