<?php

return [
    'label' => 'アフターサービス管理',

    'order_no' => '注文番号',
    'user' => 'ユーザー',
    'product' => '商品',
    'type' => 'アフターサービス種別',
    'status' => 'ステータス',
    'quantity' => '数量',
    'applied_at' => '申請日時',
    'actions' => '操作',

    'type_refund_only' => '返金のみ',
    'type_refund_and_return' => '返品＋返金',
    'type_exchange' => '交換',

    'status_pending' => '審査待ち',
    'status_approved' => '承認済み',
    'status_rejected' => '拒否済み',
    'status_in_return' => '返品中',
    'status_completed' => '完了',
    'status_canceled' => 'キャンセル済み',

    'remarks_label' => '管理者メモ（今回の操作）',
    'remarks_placeholder' => '任意、今回の審査で残すメモ。管理画面のみ表示されます',

    'action_approve' => '承認',
    'action_reject' => '拒否',
    'action_complete' => '完了にする',
    'action_cancel' => 'キャンセル',
    'no_actions' => '利用可能な操作はありません',
    'empty' => 'アフターサービスの記録はありません',

    'flash_approved' => 'アフターサービス申請を承認しました',
    'flash_rejected' => 'アフターサービス申請を拒否しました',
    'flash_completed' => 'アフターサービスを完了にしました',
    'flash_canceled' => 'アフターサービス申請をキャンセルしました',
];
