<?php

namespace App\Traits;

use App\Enums\TranslationStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;

trait HasTranslationStatus
{
    /**
     * 获取翻译状态批量操作.
     */
    public static function getTranslationStatusBulkActions(): array
    {
        return [
            BulkAction::make('set_translation_status')
                ->label(__('filament.common.bulk_set_translation_status'))
                ->icon('heroicon-o-language')
                ->form([
                    Select::make('translation_status')
                        ->label(__('filament.common.translation_status'))
                        ->options(TranslationStatusEnum::options())
                        ->required()
                        ->default(TranslationStatusEnum::Pending->value),
                ])
                ->action(function ($records, array $data) {
                    $status = TranslationStatusEnum::from($data['translation_status']);
                    $count = 0;
                    $syncService = app(\App\Services\SyncService::class);
                    $sourceNode = config('sync.node');

                    // 获取模型类（从第一个记录推断）
                    $firstRecord = $records->first();
                    if (! $firstRecord) {
                        return;
                    }
                    $modelClass = get_class($firstRecord);

                    // 禁用同步，避免每个 save() 都触发同步
                    $modelClass::$syncDisabled = true;

                    try {
                        $models = [];
                        foreach ($records as $record) {
                            $record->translation_status = $status;
                            $record->save();
                            $models[] = ['model' => $record, 'action' => 'updated'];
                            ++$count;
                        }

                        // 批量记录同步
                        if (! empty($models)) {
                            $syncService->recordBatchSync($models, $sourceNode);
                        }
                    } finally {
                        // 重新启用同步
                        $modelClass::$syncDisabled = false;
                    }

                    Notification::make()
                        ->title(__('filament.common.translation_status_updated', ['count' => $count]))
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation(),
        ];
    }
}
