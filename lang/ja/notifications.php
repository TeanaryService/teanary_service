<?php

return [
    'order_created' => [
        'title' => '注文が作成されました',
        'message' => '注文 :order_no が正常に作成されました。注文合計: :total',
    ],
    'order_paid' => [
        'title' => '注文が支払われました',
        'message' => '注文 :order_no が正常に支払われました。注文合計: :total',
    ],
    'order_status_changed' => [
        'title' => '注文ステータスが変更されました',
        'message' => '注文 :order_no のステータスが :old_status から :new_status に変更されました',
    ],
    'order_shipped' => [
        'title' => '注文が発送されました',
        'message' => '注文 :order_no が発送されました。配送方法: :shipping_method、追跡番号: :tracking_number',
    ],
    'order_cancelled' => [
        'title' => '注文がキャンセルされました',
        'message' => '注文 :order_no がキャンセルされました。理由: :reason',
    ],
    'title' => '通知センター',
    'my_notifications' => 'マイ通知',
    'no_notifications' => '通知なし',
    'no_notifications_desc' => 'まだ通知を受信していません',
    'notification' => '通知',
    'mark_as_read' => '既読にする',
    'mark_all_as_read' => 'すべて既読にする',
    'marked_as_read' => '既読にしました',
    'all_marked_as_read' => 'すべて既読にしました',
    'deleted' => '通知を削除しました',
    'confirm_delete' => 'この通知を削除してもよろしいですか？',
];
