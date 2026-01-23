<?php

// Сохраняя структуру "один файл на модуль, структура директорий соответствует Livewire",
// и одновременно позволяя использовать точечный синтаксис типа __('manager.xxx.yyy'),
// здесь мы монтируем поддиректории manager/*.php в единый manager.php.

return [
    'dashboard' => require __DIR__.'/manager/dashboard.php',

    // Бизнес-статистика / Трафик
    'traffic_statistics' => require __DIR__.'/manager/traffic_statistics.php',

    // Пользователи и адреса
    'users' => require __DIR__.'/manager/users.php',
    'addresses' => require __DIR__.'/manager/addresses.php',

    // Товары
    'products' => require __DIR__.'/manager/products.php',
    'product_variants' => require __DIR__.'/manager/product_variants.php',
    'product_reviews' => require __DIR__.'/manager/product_reviews.php',

    // Категории & Атрибуты & Спецификации
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

    // Заказы / Корзина
    'orders' => require __DIR__.'/manager/orders.php',
    'order' => require __DIR__.'/manager/orders.php',
    'order_item' => require __DIR__.'/manager/orders.php',
    'carts' => require __DIR__.'/manager/carts.php',
    'cart' => require __DIR__.'/manager/carts.php',
    'cart_item' => require __DIR__.'/manager/carts.php',

    // Маркетинг
    'promotions' => require __DIR__.'/manager/promotions.php',
    'promotion' => require __DIR__.'/manager/promotion.php',

    // Контент
    'articles' => require __DIR__.'/manager/articles.php',
    'article' => require __DIR__.'/manager/articles.php',
    'contacts' => require __DIR__.'/manager/contacts.php',
    'contact' => require __DIR__.'/manager/contacts.php',

    // Система & Настройки
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
];
