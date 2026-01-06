<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 数据同步配置
    |--------------------------------------------------------------------------
    |
    | 此配置用于管理国内外节点的双向数据同步
    |
    */

    // 是否启用同步功能
    'enabled' => env('SYNC_ENABLED', false),

    // 当前节点标识（'domestic' 或 'overseas'）
    'node' => env('SYNC_NODE', 'domestic'),

    // 远程节点配置
    'remote_nodes' => [
        'domestic' => [
            'url' => env('SYNC_DOMESTIC_URL', 'https://domestic.example.com'),
            'api_key' => env('SYNC_DOMESTIC_API_KEY', ''),
            'timeout' => env('SYNC_DOMESTIC_TIMEOUT', 30),
        ],
        'overseas' => [
            'url' => env('SYNC_OVERSEAS_URL', 'https://overseas.example.com'),
            'api_key' => env('SYNC_OVERSEAS_API_KEY', ''),
            'timeout' => env('SYNC_OVERSEAS_TIMEOUT', 30),
        ],
    ],

    // 需要同步的模型列表
    'sync_models' => [
        // 产品相关
        \App\Models\Product::class,
        \App\Models\ProductTranslation::class,
        \App\Models\ProductVariant::class,
        \App\Models\ProductReview::class,
        
        // 分类相关
        \App\Models\Category::class,
        \App\Models\CategoryTranslation::class,
        
        // 属性相关
        \App\Models\Attribute::class,
        \App\Models\AttributeTranslation::class,
        \App\Models\AttributeValue::class,
        \App\Models\AttributeValueTranslation::class,
        
        // 规格相关
        \App\Models\Specification::class,
        \App\Models\SpecificationTranslation::class,
        \App\Models\SpecificationValue::class,
        \App\Models\SpecificationValueTranslation::class,
        
        // 订单相关（可选，根据业务需求）
        // \App\Models\Order::class,
        // \App\Models\OrderItem::class,
        
        // 促销相关
        \App\Models\Promotion::class,
        \App\Models\PromotionTranslation::class,
        \App\Models\PromotionRule::class,
        
        // 文章相关
        \App\Models\Article::class,
        \App\Models\ArticleTranslation::class,
        
        // 基础数据
        \App\Models\Currency::class,
        \App\Models\Language::class,
        \App\Models\Country::class,
        \App\Models\CountryTranslation::class,
        \App\Models\Zone::class,
        \App\Models\ZoneTranslation::class,
        
        // 媒体文件（图片、资源等）
        \Spatie\MediaLibrary\MediaCollections\Models\Media::class,
    ],

    // 同步队列名称
    'queue' => env('SYNC_QUEUE', 'sync'),

    // 同步重试次数
    'retry_times' => env('SYNC_RETRY_TIMES', 3),

    // 同步重试延迟（秒）
    'retry_delay' => env('SYNC_RETRY_DELAY', 60),

    // 批量同步大小
    'batch_size' => env('SYNC_BATCH_SIZE', 100),

    // 同步超时时间（秒）
    'timeout' => env('SYNC_TIMEOUT', 300),
];
