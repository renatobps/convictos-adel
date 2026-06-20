<?php

namespace App\Filament\Resources\Inscricaos;

use App\Filament\Resources\Inscricaos\Pages\CreateInscricao;
use App\Filament\Resources\Inscricaos\Pages\EditInscricao;
use App\Filament\Resources\Inscricaos\Pages\ListInscricaos;
use App\Filament\Resources\Inscricaos\Schemas\InscricaoForm;
use App\Filament\Resources\Inscricaos\Tables\InscricaosTable;
use App\Models\Inscricao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InscricaoResource extends Resource
{
    protected static ?string $model = Inscricao::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Lead';

    protected static ?string $pluralModelLabel = 'Leads';

    protected static ?string $navigationLabel = 'Leads';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Conferência';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'novo')->count();
    }

    public static function form(Schema $schema): Schema
    {
        return InscricaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InscricaosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInscricaos::route('/'),
            'create' => CreateInscricao::route('/create'),
            'edit' => EditInscricao::route('/{record}/edit'),
        ];
    }
}
