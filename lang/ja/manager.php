<?php

// 「各モジュールごとにファイル、ディレクトリ構造を Livewire と一致させる」構造を維持しながら
// 同時に __('manager.xxx.yyy') のようなドット構文を使用できるようにするため、
// ここでサブディレクトリ manager/*.php を統一された manager.php にマウントします。

return [
    'dashboard' => require __DIR__.'/manager/dashboard.php',

    // ビジネス統計 / トラフィック
    'traffic_statistics' => require __DIR__.'/manager/traffic_statistics.php',

    // ユーザーとアドレス
    'users' => require __DIR__.'/manager/users.php',
    'addresses' => require __DIR__.'/manager/addresses.php',

    // 商品関連
    'products' => require __DIR__.'/manager/products.php',
    'product_variants' => require __DIR__.'/manager/product_variants.php',
    'product_reviews' => require __DIR__.'/manager/product_reviews.php',

    // カテゴリー & 属性 & 仕様
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

    // 注文 / カート
    'orders' => require __DIR__.'/manager/orders.php',
    'order' => require __DIR__.'/manager/orders.php',
    'order_item' => require __DIR__.'/manager/orders.php',
    'carts' => require __DIR__.'/manager/carts.php',
    'cart' => require __DIR__.'/manager/carts.php',
    'cart_item' => require __DIR__.'/manager/carts.php',

    // マーケティング
    'promotions' => require __DIR__.'/manager/promotions.php',
    'promotion' => require __DIR__.'/manager/promotion.php',

    // コンテンツ
    'articles' => require __DIR__.'/manager/articles.php',
    'article' => require __DIR__.'/manager/articles.php',
    'contacts' => require __DIR__.'/manager/contacts.php',
    'contact' => require __DIR__.'/manager/contacts.php',

    // システム & 設定
    'countries' => require __DIR__.'/manager/countries.php',
    'country' => require __DIR__.'/manager/countries.php',
    'zones' => require __DIR__.'/manager/zones.php',
    'zone' => require __DIR__.'/manager/zones.php',
    'currencies' => require __DIR__.'/manager/currencies.php',
    'currency' => require __DIR__.'/manager/currencies.php',
    'languages' => require __DIR__.'/manager/languages.php',
    'language' => require __DIR__.'/manager/languages.php',
    'managers' => require __DIR__.'/manager/managers.php',
    'manager' => require __DIR__.'/manager/managers.php',
    'users' => require __DIR__.'/manager/users.php',
    'user' => require __DIR__.'/manager/users.php',

    // 一括操作
    'batch' => require __DIR__.'/manager/batch.php',
];
