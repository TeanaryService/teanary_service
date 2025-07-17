<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CategoryResource\Pages;
use App\Filament\Manager\Resources\CategoryResource\RelationManagers;
use App\Services\LocaleCurrencyService;
use App\Models\Category;
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

class CategoryResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Category::class;

    public static function getLabel(): string
    {
        return __('filament.CategoryResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.CategoryResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.CategoryResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.CategoryResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.CategoryResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.CategoryResource.sort');
    }

    public static function form(Form $form): Form
    {
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
        $currentId = $form->getModelInstance()?->id;

        // 获取所有一级分类（parent_id 为 null，且不是当前分类）
        $categories = Category::with('categoryTranslations')
            ->whereNull('parent_id')
            ->when($currentId, fn($q) => $q->where('id', '!=', $currentId))
            ->get();

        $options = [];
        foreach ($categories as $cat) {
            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
            if ($translation && $translation->name) {
                $options[$cat->id] = $translation->name . "({$cat->id})";
            } else {
                $first = $cat->categoryTranslations->first();
                $options[$cat->id] = ($first ? $first->name : $cat->slug) . "({$cat->id})";
            }
        }

        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label(__('filament_category.parent'))
                    ->options($options)
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\TextInput::make('slug')
                    ->label(__('filament_category.slug'))
                    ->required()
                    ->maxLength(255),

                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->categoryTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return Forms\Components\TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament_category.name') . " ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament_category.name')),
            ]);
    }

    public static function table(Table $table): Table
    {
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('parent_id')
                    ->label(__('filament_category.parent'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $parent = $record->category;
                        if (!$parent) {
                            return null;
                        }
                        $translation = $parent->categoryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name . "({$parent->id})";
                        }
                        $first = $parent->categoryTranslations->first();
                        return ($first ? $first->name : $parent->slug) . "({$parent->id})";
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament_category.slug'))
                    ->searchable(),
                // 显示当前语言的 name
                Tables\Columns\TextColumn::make('categoryTranslations.name')
                    ->label(__('filament_category.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        return optional(
                            $record->categoryTranslations->where('language_id', $lang?->id)->first()
                        )->name;
                    }),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
