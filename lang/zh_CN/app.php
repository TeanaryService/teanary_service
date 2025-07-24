<?php

return [
    'home' => '首页',
    'add_cart_success' => '已添加到购物车',
    'edit_cart_success' => '购物车已更新',
    'delete_cart_success' => '已从购物车删除',
    'email_verify_subject' => '验证你的邮箱地址',
    'email_verify_line1' => '点击下面的按钮以验证你的邮箱。',
    'email_verify_action' => '验证邮箱',
    'email_verify_line2' => '如果你没有创建账号，则无需采取进一步操作。',
    'review_submitted' => '评论已提交',
    'select_shipping_address' => '请选择配送地址',
    'order_items' => '订单商品',

    'shipping' => [
        'method' => [
            'sf_international' => '顺丰国际',
        ],
        'description' => [
            'sf' => '预计 :days 天送达',
        ],
    ],

    'payment' => [
        'method' => [
            'paypal' => 'PayPal',
        ],
    ],

    'promotion' => [
        'type' => [
            'coupon' => '优惠券',
            'automatic' => '自动促销',
        ],
        'discount_type' => [
            'fixed' => '固定金额',
            'percentage' => '百分比',
        ],
        'condition' => [
            'order_total_min' => '最小订单金额',
            'order_qty_min' => '最小订单数量',
        ],
    ],

    'product' => [
        'status' => [
            'active' => '上架',
            'inactive' => '下架',
        ],
    ],

    'order' => [
        'status' => [
            'pending' => '待付款',
            'paid' => '已付款',
            'shipped' => '已发货', 
            'completed' => '已完成',
            'cancelled' => '已取消',
        ],
    ],
];
