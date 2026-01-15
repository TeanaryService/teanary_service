<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

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
        return $form->schema([
            Forms\Components\Tabs::make('article_tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('basic')
                        ->label(__('filament.article.basic_info'))
                        ->schema([
                            ...static::getArticleBaseFields(),
                        ]),
                    Forms\Components\Tabs\Tab::make('translations')
                        ->label(__('filament.article.translations'))
                        ->schema(static::getArticleTranslationsTabs($form)),
                ])
                ->columnSpanFull(),
        ]);
    }

    protected static function getArticleBaseFields(): array
    {
        return [
            Forms\Components\Section::make(__('filament.article.basic_info'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('image')
                        ->label(__('filament.article.image'))
                        ->image()
                        ->imageEditor()
                        ->imageCropAspectRatio('16:9')
                        ->columnSpanFull()
                        ->required()
                        ->collection('image')
                        ->helperText(__('filament.article.image_helper')),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->label(__('filament.article.slug'))
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('filament.article.slug_helper'))
                        ->columnSpan(2),
                    Forms\Components\Select::make('user_id')
                        ->label(__('filament.article.user_id'))
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->default(null)
                        ->helperText(__('filament.article.user_id_helper')),
                    Forms\Components\Toggle::make('is_published')
                        ->label(__('filament.article.is_published'))
                        ->default(false)
                        ->inline(false)
                        ->columnSpan(1),
                    Forms\Components\Select::make('translation_status')
                        ->label(__('filament.article.translation_status'))
                        ->options(TranslationStatusEnum::options())
                        ->default(TranslationStatusEnum::NotTranslated->value)
                        ->required()
                        ->columnSpan(1),
                ])
                ->columns(3),
        ];
    }

    protected static function getArticleTranslationsTabs(Form $form): array
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return [
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
                                    ->maxLength(255)
                                    ->default($translation ? $translation->title : '')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make("translations.{$lang->id}.summary")
                                    ->label(__('filament.article.summary'))
                                    ->required($lang->is_default ?? false)
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->default($translation ? $translation->summary : '')
                                    ->columnSpanFull()
                                    ->helperText(__('filament.article.summary_helper')),
                                reusableRichEditor("translations.{$lang->id}.content", $translation ? ($translation->content ?? '') : '', __('filament.article.content'), $lang->id),
                            ]);
                    })->toArray()
                )
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with([
                        'user',
                        'articleTranslations',
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('filament.article.image'))
                    ->collection('image')
                    ->conversion('thumb')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('filament.article.title'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->articleTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->title) {
                            return $translation->title;
                        }
                        $first = $record->articleTranslations->first();
                        return $first ? $first->title : $record->slug;
                    })
                    ->searchable(query: function (Builder $query, string $search) use ($lang): Builder {
                        return $query->whereHas('articleTranslations', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%")
                                ->orWhere('summary', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction) use ($lang): Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('article_translations', function ($join) use ($langId) {
                            $join->on('articles.id', '=', 'article_translations.article_id')
                                ->where('article_translations.language_id', '=', $langId);
                        })
                        ->orderBy('article_translations.title', $direction)
                        ->select('articles.*')
                        ->groupBy('articles.id');
                    })
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('summary')
                    ->label(__('filament.article.summary'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->articleTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->summary) {
                            return $translation->summary;
                        }
                        $first = $record->articleTranslations->first();
                        return $first ? ($first->summary ?? '') : '';
                    })
                    ->limit(80)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament.article.slug'))
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('filament.article.is_published'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.article.user_id'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.article.translation_status'))
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
                Tables\Filters\SelectFilter::make('is_published')
                    ->label(__('filament.article.is_published'))
                    ->options([
                        1 => __('filament.article.published'),
                        0 => __('filament.article.unpublished'),
                    ]),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.article.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('filament.article.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                    ...static::getTranslationStatusBulkActions(),
                    static::getBulkPublishAction(),
                    static::getBulkUnpublishAction(),
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }

    /**
     * 获取批量发布文章的批量操作.
     */
    public static function getBulkPublishAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('bulk_publish')
            ->label(__('filament.article.bulk_publish'))
            ->icon('heroicon-o-check-circle')
            ->action(function ($records) {
                $count = 0;
                foreach ($records as $record) {
                    $record->is_published = true;
                    $record->save();
                    ++$count;
                }

                \Filament\Notifications\Notification::make()
                    ->title(__('filament.article.bulk_publish_success', ['count' => $count]))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }

    /**
     * 获取批量取消发布文章的批量操作.
     */
    public static function getBulkUnpublishAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('bulk_unpublish')
            ->label(__('filament.article.bulk_unpublish'))
            ->icon('heroicon-o-x-circle')
            ->action(function ($records) {
                $count = 0;
                foreach ($records as $record) {
                    $record->is_published = false;
                    $record->save();
                    ++$count;
                }

                \Filament\Notifications\Notification::make()
                    ->title(__('filament.article.bulk_unpublish_success', ['count' => $count]))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }
}
