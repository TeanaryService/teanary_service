<?php

namespace App\Traits;

use App\Enums\TranslationStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;

trait HasTranslationStatus
{
    /**
     * 获取翻译状态批量操作
     */
    public static function getTranslationStatusBulkActions(): array
    {
        return [
            BulkAction::make('set_translation_status')
                ->label('批量设置翻译状态')
                ->icon('heroicon-o-language')
                ->form([
                    Select::make('translation_status')
                        ->label('翻译状态')
                        ->options(TranslationStatusEnum::options())
                        ->required()
                        ->default(TranslationStatusEnum::Pending->value),
                ])
                ->action(function ($records, array $data) {
                    $status = TranslationStatusEnum::from($data['translation_status']);
                    $count = 0;
                    
                    foreach ($records as $record) {
                        $record->translation_status = $status;
                        $record->save();
                        $count++;
                    }
                    
                    Notification::make()
                        ->title("已更新 {$count} 条记录的翻译状态")
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation(),
        ];
    }
}
