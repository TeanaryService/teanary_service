<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArticleResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Article::class;
    protected static ?int $navigationSort = 104;

    public static function getLabel(): string
    {
        return __('filament.ArticleResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ArticleResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ArticleResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ArticleResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ArticleResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label(__('filament.article.image'))
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('16:9')
                    ->columnSpanFull()
                    ->required()
                    ->collection('image'),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->label(__('filament.article.slug'))
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->label(__('filament.article.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\Toggle::make('is_published')
                    ->label(__('filament.article.is_published'))
                    ->required(),

                Tabs::make('translations_tabs')
                    ->tabs(
                        $languages->map(function ($lang) use ($model) {
                            $translation = null;
                            if ($model && $model->exists) {
                                $translation = $model->articleTranslations
                                    ->where('language_id', $lang->id)
                                    ->first();
                            }
                            return Tabs\Tab::make($lang->name)
                                ->schema([
                                    Forms\Components\TextInput::make("translations.{$lang->id}.title")
                                        ->label(__('filament.article.title'))
                                        ->required($lang->is_default ?? false)
                                        ->default($translation ? $translation->title : ''),

                                    Forms\Components\TextInput::make("translations.{$lang->id}.summary")
                                        ->label(__('filament.article.summary'))
                                        ->required($lang->is_default ?? false)
                                        ->default($translation ? $translation->summary : ''),

                                    reusableRichEditor("translations.{$lang->id}.content", $translation?->content ?? '', __('filament.article.content'), $lang->id),

                                ]);
                        })->toArray()
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn(Builder $query): Builder => $query
                    ->with([
                        'articleTranslations',
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('filament.article.image'))
                    ->collection('image')
                    ->conversion('thumb'),
                // 多语言 name 列
                Tables\Columns\TextColumn::make('articleTranslations.title')
                    ->label(__('filament.article.title'))
                    ->limit(64)
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->articleTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->title;
                        }
                        $first = $record->articleTranslations->first();
                        return $first ? $first->title : '';
                    }),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament.article.slug'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('filament.article.is_published'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.article.user_id')),
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
