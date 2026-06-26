<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait RestrictsByRegional
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && ($user->isAdmin() || $user->isRegionalLeader());
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected static function applyRegionalScope(Builder $query, string $relationPath = 'regional_id'): Builder
    {
        $user = Auth::user();
        if ($user === null || $user->isAdmin()) {
            return $query;
        }

        $scopeIds = $user->regionalScopeIds();
        if (empty($scopeIds)) {
            return $query->whereRaw('1 = 0');
        }

        if (! str_contains($relationPath, '.')) {
            return $query->whereIn($relationPath, $scopeIds);
        }

        [$relation, $column] = explode('.', $relationPath, 2);

        return $query->whereHas($relation, fn (Builder $q) => $q->whereIn($column, $scopeIds));
    }
}
