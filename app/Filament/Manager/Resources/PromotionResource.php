<?php

namespace App\Filament\Manager\Resources;

use App\Enums\PromotionTypeEnum;
use App\Filament\Manager\Resources\PromotionResource\Pages;
use App\Filament\Manager\Resources\PromotionResource\RelationManagers;
use App\Models\Promotion;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $pluralLabel = '促销活动';
    protected static ?string $label = '促销活动';
    protected static ?int $navigationSort = 101;
    protected static ?string $navigationGroup = '商务运营';
    protected static ?string $navigationLabel = '促销活动';
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('活动类型')
                    ->options(PromotionTypeEnum::options())
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('开始时间')
                    ->displayFormat('Y-m-d H:i:s'),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('结束时间')
                    ->displayFormat('Y-m-d H:i:s'),
                Forms\Components\Toggle::make('active')
                    ->label('是否启用')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn($state): string => $state->label())
                    ->label('活动类型'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('开始时间')
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('结束时间')
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('是否启用')
                    ->boolean(),
                ...static::getTimestampsColumns()
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions()
                ]),
            ]));
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
