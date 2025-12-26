<?php

namespace App\Traits;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

trait HasDefaultPagination
{
    public static function applyDefaultPagination(Table $table): Table
    {
        $columns = $table->getColumns();
        $newColumns = static::withIdColumn($columns);

        return $table
            ->striped()
            ->recordUrl(null)
            ->columns($newColumns)
            // ->filtersLayout(FiltersLayout::AboveContent)
            ->defaultSort('id', 'desc')
            ->paginated([15, 25])
            ->defaultPaginationPageOption(15);
    }

    protected static function withIdColumn(array $columns): array
    {
        return array_merge([
            TextColumn::make('id')->label('ID')->sortable(),
        ], $columns);
    }
}
