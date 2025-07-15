<?php

namespace App\Traits;

use Filament\Tables;

trait HasTimestampsColumn
{
    public static function getTimestampsColumns(
        bool $hideCreatedAt = true,
        bool $hideUpdatedAt = true
    ): array {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->label('创建时间')
                ->dateTime(format: 'Y-m-d H:i:s')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: $hideCreatedAt),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('更新时间')
                ->dateTime(format: 'Y-m-d H:i:s')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: $hideUpdatedAt),
        ];
    }
}
