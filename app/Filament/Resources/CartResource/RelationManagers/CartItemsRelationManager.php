<?php

namespace App\Filament\Resources\CartResource\RelationManagers;

use App\Filament\Resources\CartItemResource;
use App\Models\CartItem;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';

    public static function getLabel(): string
    {
        return __('filament.cart.cart_items');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.cart.cart_items');
    }

    public function form(Form $form): Form
    {
        return CartItemResource::form($form->columns(1));
    }

    public function table(Table $table): Table
    {
        return CartItemResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.cart.cart_items'))
                    ->after(function (CartItem $record, array $data): void {
                        $this->hydrateProductVariantLabel($record);
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make()
                    ->after(function (CartItem $record, array $data): void {
                        $this->hydrateProductVariantLabel($record);
                    }),
            ]);
    }

    /**
     * Optional: 为编辑或创建后的记录动态刷新变体翻译标签（仅用于刷新界面）.
     */
    protected function hydrateProductVariantLabel(CartItem $record): void
    {
        $variant = $record->productVariant;

        if ($variant && ! $variant->relationLoaded('specificationValues')) {
            $variant->load('specificationValues.specificationValueTranslations');
        }

        // 此处不返回任何内容，仅用于刷新记录数据，如果你用的是 Livewire 模式刷新表格，不一定需要。
    }
}
