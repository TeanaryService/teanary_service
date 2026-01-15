<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ContactResource\Pages;
use App\Models\Contact;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Contact::class;

    protected static ?int $navigationSort = 105;

    public static function getLabel(): string
    {
        return __('filament.ContactResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.ContactResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.ContactResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.ContactResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.ContactResource.icon');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.contact.basic_info'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.contact.name'))
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('email')
                            ->label(__('filament.contact.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('message')
                            ->label(__('filament.contact.message'))
                            ->rows(5)
                            ->columnSpanFull()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.contact.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.contact.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('message')
                    ->label(__('filament.contact.message'))
                    ->limit(100)
                    ->wrap()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('filament.contact.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('filament.contact.created_until')),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
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
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
