<?php

return [
    'order_created' => [
        'title' => '订单已创建',
        'message' => '您的订单 :order_no 已创建成功，订单金额：:total',
    ],
    'order_paid' => [
        'title' => '订单支付成功',
        'message' => '您的订单 :order_no 已支付成功，订单金额：:total',
    ],
    'order_status_changed' => [
        'title' => '订单状态变更',
        'message' => '您的订单 :order_no 状态已从 :old_status 变更为 :new_status',
    ],
    'order_shipped' => [
        'title' => '订单已发货',
        'message' => '您的订单 :order_no 已发货，物流方式：:shipping_method，运单号：:tracking_number',
    ],
    'order_cancelled' => [
        'title' => '订单已取消',
        'message' => '您的订单 :order_no 已取消，原因：:reason',
    ],
    'title' => '通知中心',
    'my_notifications' => '我的通知',
    'no_notifications' => '暂无通知',
    'no_notifications_desc' => '您还没有收到任何通知',
    'notification' => '通知',
    'mark_as_read' => '标记为已读',
    'mark_all_as_read' => '全部标记为已读',
    'marked_as_read' => '已标记为已读',
    'all_marked_as_read' => '已全部标记为已读',
    'deleted' => '通知已删除',
    'confirm_delete' => '确定要删除这条通知吗？',
];
