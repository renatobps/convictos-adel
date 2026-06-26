<?php

namespace App\Filament\Resources\Inscricaos;

use App\Filament\Concerns\RestrictsByRegional;
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
use Illuminate\Database\Eloquent\Builder;

class InscricaoResource extends Resource
{
    use RestrictsByRegional;

    protected static ?string $model = Inscricao::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Inscrição';

    protected static ?string $pluralModelLabel = 'Inscrições';

    protected static ?string $navigationLabel = 'Inscrições';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Conferência';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', Inscricao::STATUS_AGUARDANDO)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * @return Builder<Inscricao>
     */
    public static function getEloquentQuery(): Builder
    {
        return static::applyRegionalScope(parent::getEloquentQuery(), 'igrejaRel.regional_id');
    }

    public static function form(Schema $schema): Schema
    {
        return InscricaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InscricaosTable::configure($table);
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
