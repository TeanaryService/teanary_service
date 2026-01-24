<?php

// "각 모듈당 하나의 파일, 디렉토리 구조를 Livewire와 일치시키기" 구조를 유지하면서
// 동시에 __('manager.xxx.yyy') 같은 점 구문을 사용할 수 있도록 하기 위해,
// 여기서 하위 디렉토리 manager/*.php를 통합된 manager.php에 마운트합니다.

return [
    'dashboard' => require __DIR__.'/manager/dashboard.php',

    // 비즈니스 통계 / 트래픽
    'traffic_statistics' => require __DIR__.'/manager/traffic_statistics.php',

    // 사용자 및 주소
    'users' => require __DIR__.'/manager/users.php',
    'addresses' => require __DIR__.'/manager/addresses.php',

    // 제품 관련
    'products' => require __DIR__.'/manager/products.php',
    'product_variants' => require __DIR__.'/manager/product_variants.php',
    'product_reviews' => require __DIR__.'/manager/product_reviews.php',

    // 카테고리 & 속성 & 사양
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

    // 주문 / 장바구니
    'orders' => require __DIR__.'/manager/orders.php',
    'order' => require __DIR__.'/manager/orders.php',
    'order_item' => require __DIR__.'/manager/orders.php',
    'carts' => require __DIR__.'/manager/carts.php',
    'cart' => require __DIR__.'/manager/carts.php',
    'cart_item' => require __DIR__.'/manager/carts.php',

    // 마케팅
    'promotions' => require __DIR__.'/manager/promotions.php',
    'promotion' => require __DIR__.'/manager/promotion.php',

    // 콘텐츠
    'articles' => require __DIR__.'/manager/articles.php',
    'article' => require __DIR__.'/manager/articles.php',
    'contacts' => require __DIR__.'/manager/contacts.php',
    'contact' => require __DIR__.'/manager/contacts.php',

    // 시스템 & 설정
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

    // 일괄 작업
    'batch' => require __DIR__.'/manager/batch.php',
];
