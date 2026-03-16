<?php

// 为了保持「每个模块一个文件、目录结构与 Livewire 一致」的同时
// 仍然可以使用 __('manager.xxx.yyy') 这种点语法，
// 这里把子目录 manager/*.php 挂载到一个统一的 manager.php 上。

return [
    'dashboard' => require __DIR__.'/manager/dashboard.php',

    // 业务统计 / 流量
    'traffic_statistics' => require __DIR__.'/manager/traffic_statistics.php',

    // 用户与地址
    'users' => require __DIR__.'/manager/users.php',
    'addresses' => require __DIR__.'/manager/addresses.php',

    // 商品相关
    'products' => require __DIR__.'/manager/products.php',
    'product_variants' => require __DIR__.'/manager/product_variants.php',
    'product_reviews' => require __DIR__.'/manager/product_reviews.php',

    // 分类 & 属性 & 规格
    'categories' => require __DIR__.'/manager/categories.php',
    'category' => require __DIR__.'/manager/categories.php',
    'attributes' => require __DIR__.'/manager/attributes.php',
    'attribute' => require __DIR__.'/manager/attributes.php',
    'attribute_values' => require __DIR__.'/manager/attribute_values.php',
    'attribute_value' => require __DIR__.'/manager/attribute_values.php',
    'specifications' => require __DIR__.'/manager/specifications.php',
    'specification' => require __DIR__.'/manager/specifications.php',
    'specification_values' => require __DIR__.'/manager/specification_values.php',
    'specification_value' => require __DIR__.'/manager/specification_values.php',

    // 订单 / 购物车
    'orders' => require __DIR__.'/manager/orders.php',
    'order' => require __DIR__.'/manager/orders.php',
    'order_item' => require __DIR__.'/manager/orders.php',
    'carts' => require __DIR__.'/manager/carts.php',
    'cart' => require __DIR__.'/manager/carts.php',
    'cart_item' => require __DIR__.'/manager/carts.php',

    // 营销
    'promotions' => require __DIR__.'/manager/promotions.php',
    'promotion' => require __DIR__.'/manager/promotion.php',

    // 内容
    'articles' => require __DIR__.'/manager/articles.php',
    'article' => require __DIR__.'/manager/articles.php',
    'contacts' => require __DIR__.'/manager/contacts.php',
    'contact' => require __DIR__.'/manager/contacts.php',

    // 系统 & 配置
    'countries' => require __DIR__.'/manager/countries.php',
    'country' => require __DIR__.'/manager/countries.php',
    'zones' => require __DIR__.'/manager/zones.php',
    'zone' => require __DIR__.'/manager/zones.php',
    'currencies' => require __DIR__.'/manager/currencies.php',
    'currency' => require __DIR__.'/manager/currencies.php',
    'languages' => require __DIR__.'/manager/languages.php',
    'language' => require __DIR__.'/manager/languages.php',
    'warehouses' => require __DIR__.'/manager/warehouses.php',
    'warehouse' => require __DIR__.'/manager/warehouse.php',
    'managers' => require __DIR__.'/manager/managers.php',
    'manager' => require __DIR__.'/manager/managers.php',
    'users' => require __DIR__.'/manager/users.php',
    'user' => require __DIR__.'/manager/users.php',

    // 批量操作
    'batch' => require __DIR__.'/manager/batch.php',
    'after_sales' => require __DIR__.'/manager/after_sales.php',
];
