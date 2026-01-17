<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\TrafficStatisticResource\Pages;
use App\Models\TrafficStatistic;
use App\Traits\HasDefaultPagination;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class TrafficStatisticResource extends Resource
{
    use HasDefaultPagination;

    protected static ?string $model = TrafficStatistic::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 51;

    protected static ?string $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.TrafficStatistics.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.TrafficStatisticResource.navigation_label');
    }

    public static function getLabel(): string
    {
        return __('filament.TrafficStatisticResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.TrafficStatisticResource.pluralLabel');
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('stat_date')
                    ->label(__('filament.TrafficStatisticResource.stat_date'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('path')
                    ->label(__('filament.TrafficStatisticResource.path'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->path),
                Tables\Columns\TextColumn::make('method')
                    ->label(__('filament.TrafficStatisticResource.method'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'GET' => 'success',
                        'POST' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip')
                    ->label(__('filament.TrafficStatisticResource.ip'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_bot')
                    ->label(__('filament.TrafficStatisticResource.is_bot'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('spider_source')
                    ->label(__('filament.TrafficStatisticResource.spider_source'))
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('count')
                    ->label(__('filament.TrafficStatisticResource.count'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => Number::format($state)),
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('filament.TrafficStatisticResource.locale'))
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('referer')
                    ->label(__('filament.TrafficStatisticResource.referer'))
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->referer)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label(__('filament.TrafficStatisticResource.user_agent'))
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.TrafficStatisticResource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_bot')
                    ->label(__('filament.TrafficStatisticResource.is_bot'))
                    ->options([
                        false => __('filament.TrafficStatistics.human'),
                        true => __('filament.TrafficStatistics.bot'),
                    ]),
                Tables\Filters\SelectFilter::make('spider_source')
                    ->label(__('filament.TrafficStatisticResource.spider_source'))
                    ->options([
                        'google' => 'Google',
                        'bing' => 'Bing',
                        'baidu' => 'Baidu',
                        'yandex' => 'Yandex',
                        'yahoo' => 'Yahoo',
                        'duckduckgo' => 'DuckDuckGo',
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter',
                        'linkedin' => 'LinkedIn',
                        'other' => __('filament.TrafficStatisticResource.other'),
                        'unknown' => __('filament.TrafficStatisticResource.unknown'),
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('stat_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('stat_date_from')
                            ->label(__('filament.TrafficStatisticResource.stat_date_from')),
                        \Filament\Forms\Components\DatePicker::make('stat_date_until')
                            ->label(__('filament.TrafficStatisticResource.stat_date_until')),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['stat_date_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('stat_date', '>=', $date),
                            )
                            ->when(
                                $data['stat_date_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('stat_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // 不需要查看详情操作
            ])
            ->bulkActions([
                // 只读，不提供批量操作
            ])
            ->defaultSort('stat_date', 'desc'));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getSlug(): string
    {
        return 'traffic-statistics-records';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrafficStatistics::route('/'),
        ];
    }
}
