<?php

namespace App\Filament\Resources\Igrejas;

use App\Filament\Concerns\RestrictsByRegional;
use App\Filament\Resources\Igrejas\Pages\CreateIgreja;
use App\Filament\Resources\Igrejas\Pages\EditIgreja;
use App\Filament\Resources\Igrejas\Pages\ListIgrejas;
use App\Filament\Resources\Igrejas\Schemas\IgrejaForm;
use App\Filament\Resources\Igrejas\Tables\IgrejasTable;
use App\Models\Igreja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IgrejaResource extends Resource
{
    use RestrictsByRegional;

    protected static ?string $model = Igreja::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $modelLabel = 'Igreja';

    protected static ?string $pluralModelLabel = 'Igrejas';

    protected static ?string $navigationLabel = 'Igrejas';

    protected static ?int $navigationSort = 3;

    protected static string|\UnitEnum|null $navigationGroup = 'ADEL';

    /**
     * @return Builder<Igreja>
     */
    public static function getEloquentQuery(): Builder
    {
        return static::applyRegionalScope(parent::getEloquentQuery(), 'regional_id');
    }

    public static function form(Schema $schema): Schema
    {
        return IgrejaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IgrejasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIgrejas::route('/'),
            'create' => CreateIgreja::route('/create'),
            'edit' => EditIgreja::route('/{record}/edit'),
        ];
    }
}
