<?php

// Um die Struktur "ein Modul pro Datei, Verzeichnisstruktur konsistent mit Livewire" beizubehalten
// und gleichzeitig die Punkt-Syntax __('manager.xxx.yyy') verwenden zu können,
// werden hier die Unterverzeichnisse manager/*.php in eine einheitliche manager.php eingebunden.

return [
    'dashboard' => require __DIR__.'/manager/dashboard.php',

    // Geschäftsstatistiken / Traffic
    'traffic_statistics' => require __DIR__.'/manager/traffic_statistics.php',

    // Benutzer & Adressen
    'users' => require __DIR__.'/manager/users.php',
    'addresses' => require __DIR__.'/manager/addresses.php',

    // Produktbezogen
    'products' => require __DIR__.'/manager/products.php',
    'product_variants' => require __DIR__.'/manager/product_variants.php',
    'product_reviews' => require __DIR__.'/manager/product_reviews.php',

    // Kategorien & Attribute & Spezifikationen
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

    // Bestellungen / Warenkorb
    'orders' => require __DIR__.'/manager/orders.php',
    'order' => require __DIR__.'/manager/orders.php',
    'order_item' => require __DIR__.'/manager/orders.php',
    'carts' => require __DIR__.'/manager/carts.php',
    'cart' => require __DIR__.'/manager/carts.php',
    'cart_item' => require __DIR__.'/manager/carts.php',

    // Marketing
    'promotions' => require __DIR__.'/manager/promotions.php',
    'promotion' => require __DIR__.'/manager/promotion.php',

    // Inhalte
    'articles' => require __DIR__.'/manager/articles.php',
    'article' => require __DIR__.'/manager/articles.php',
    'contacts' => require __DIR__.'/manager/contacts.php',
    'contact' => require __DIR__.'/manager/contacts.php',

    // System & Konfiguration
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

    // Massenoperationen
    'batch' => require __DIR__.'/manager/batch.php',
    'after_sales' => require __DIR__.'/manager/after_sales.php',
];
