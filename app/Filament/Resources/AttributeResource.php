<?php

namespace App\Filament\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Resources\AttributeResource\Pages;
use App\Filament\Resources\AttributeResource\RelationManagers\AttributeValuesRelationManager;
use App\Models\Attribute;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class AttributeResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = Attribute::class;

    protected static ?int $navigationSort = 203;

    public static function getLabel(): string
    {
        return __('filament.AttributeResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.AttributeResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.AttributeResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.AttributeResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.AttributeResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                Forms\Components\Toggle::make('is_filterable')
                    ->label(__('filament.attribute.is_filterable'))
                    ->default(false)
                    ->helperText(__('filament.attribute.is_filterable_helper'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('translation_status')
                    ->label('翻译状态')
                    ->options(TranslationStatusEnum::options())
                    ->default(TranslationStatusEnum::NotTranslated->value)
                    ->required(),
                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->attributeTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament.attribute.name')." ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament.attribute.name')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                // 显示当前语言的 name
                TextColumn::make('attributeTranslations.name')
                    ->label(__('filament.attribute.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

                        return optional(
                            $record->attributeTranslations->where('language_id', $lang?->id)->first()
                        )->name;
                    }),
                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('filament.attribute.is_filterable'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label('翻译状态')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    }),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions(),
                    ...static::getTranslationStatusBulkActions(),
                    static::getIsFilterableBulkAction(),
                ]),
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
            AttributeValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }

    /**
     * 获取前台是否可见的批量操作
     */
    public static function getIsFilterableBulkAction(): BulkAction
    {
        return BulkAction::make('set_is_filterable')
            ->label('批量修改前台是否可见')
            ->icon('heroicon-o-eye')
            ->form([
                Toggle::make('is_filterable')
                    ->label('前台是否可见')
                    ->default(true)
                    ->required(),
            ])
            ->action(function ($records, array $data) {
                $isFilterable = (bool) $data['is_filterable'];
                $count = 0;

                foreach ($records as $record) {
                    $record->is_filterable = $isFilterable;
                    $record->save();
                    $count++;
                }

                // 清除属性缓存
                Cache::forget('attributes.with.translations');

                Notification::make()
                    ->title("已更新 {$count} 条记录的前台可见状态")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }
}
