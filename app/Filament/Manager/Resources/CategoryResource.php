<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 202;

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

    public static function form(Form $form): Form
    {
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
        $currentId = $form->getModelInstance()?->id;

        // 获取所有一级分类（parent_id 为 null，且不是当前分类）
        $categories = Category::with('categoryTranslations')
            ->whereNull('parent_id')
            ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
            ->get();

        $options = [];
        foreach ($categories as $cat) {
            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
            if ($translation && $translation->name) {
                $options[$cat->id] = $translation->name."({$cat->id})";
            } else {
                $first = $cat->categoryTranslations->first();
                $options[$cat->id] = ($first ? $first->name : $cat->slug)."({$cat->id})";
            }
        }

        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.category.basic_info'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label(__('filament.category.image'))
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->columnSpanFull()
                            ->required()
                            ->collection('image')
                            ->helperText(__('filament.category.image_helper')),
                        Forms\Components\Select::make('parent_id')
                            ->label(__('filament.category.parent'))
                            ->options($options)
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->columnSpan(1)
                            ->helperText(__('filament.category.parent_helper')),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('filament.category.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1)
                            ->helperText(__('filament.category.slug_helper')),
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.category.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Forms\Components\Section::make(__('filament.category.translations'))
                    ->schema([
                        Forms\Components\Group::make(
                            $languages->map(function ($language) use ($model) {
                                $default = '';
                                if ($model && $model->exists) {
                                    $translation = $model->categoryTranslations
                                        ->where('language_id', $language->id)
                                        ->first();
                                    $default = $translation ? $translation->name : '';
                                }

                                return Forms\Components\TextInput::make("translations.{$language->id}.name")
                                    ->label(__('filament.category.name')." ({$language->name})")
                                    ->required($language->is_default ?? false)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($language->is_default ? __('filament.category.name_helper') : null);
                            })->toArray()
                        )->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                    ->with([
                        'category.categoryTranslations',
                        'categoryTranslations',
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('filament.category.image'))
                    ->collection('image')
                    ->conversion('thumb')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.category.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->categoryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->categoryTranslations->first();
                        return $first ? $first->name : __('filament.category.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('categoryTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('category_translations', function ($join) use ($langId) {
                            $join->on('categories.id', '=', 'category_translations.category_id')
                                ->where('category_translations.language_id', '=', $langId);
                        })
                        ->orderBy('category_translations.name', $direction)
                        ->select('categories.*')
                        ->groupBy('categories.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('filament.category.parent'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $parent = $record->category;
                        if (! $parent) {
                            return __('filament.category.root');
                        }
                        $translation = $parent->categoryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $parent->categoryTranslations->first();
                        return $first ? $first->name : $parent->slug;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament.category.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('filament.category.products_count'))
                    ->counts('products')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.category.translation_status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->sortable()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('filament.category.parent'))
                    ->relationship('category', 'id', function ($query) {
                        return $query->with('categoryTranslations')->whereNull('parent_id');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->categoryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->categoryTranslations->first();
                        return $first ? $first->name : $record->slug;
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.category.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple(),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                    ...static::getTranslationStatusBulkActions(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'));
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
