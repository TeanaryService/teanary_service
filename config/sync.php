<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 数据同步配置
    |--------------------------------------------------------------------------
    |
    | 此配置用于管理多个节点之间的双向数据同步
    | 支持任意数量的节点，根据配置中的节点数量自动同步
    |
    */

    // 是否启用同步功能
    'enabled' => env('SYNC_ENABLED', false),

    // 当前节点标识（可以是任意字符串，如 'node1', 'beijing', 'shanghai' 等）
    'node' => env('SYNC_NODE', 'node1'),

    // 远程节点配置
    // 可以配置任意数量的节点，系统会自动同步到所有其他节点
    // 节点名称可以是任意字符串，建议使用有意义的名称
    'remote_nodes' => [
        // 示例：节点1配置
        'node1' => [
            'url' => env('SYNC_NODE1_URL', 'https://node1.example.com'),
            'api_key' => env('SYNC_NODE1_API_KEY', ''),
            'timeout' => env('SYNC_NODE1_TIMEOUT', 600), // 默认10分钟，适应跨国网络延迟
        ],
        // 示例：节点2配置
        'node2' => [
            'url' => env('SYNC_NODE2_URL', 'https://node2.example.com'),
            'api_key' => env('SYNC_NODE2_API_KEY', ''),
            'timeout' => env('SYNC_NODE2_TIMEOUT', 600), // 默认10分钟，适应跨国网络延迟
        ],
        // 可以继续添加更多节点...
        // 'node3' => [
        //     'url' => env('SYNC_NODE3_URL', 'https://node3.example.com'),
        //     'api_key' => env('SYNC_NODE3_API_KEY', ''),
        //     'timeout' => env('SYNC_NODE3_TIMEOUT', 600), // 默认10分钟，适应跨国网络延迟
        // ],
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
        
        // 订单相关
        \App\Models\Order::class,
        \App\Models\OrderItem::class,
        \App\Models\OrderShipment::class,
        
        // 购物车相关
        \App\Models\Cart::class,
        \App\Models\CartItem::class,
        
        // 促销相关
        \App\Models\Promotion::class,
        \App\Models\PromotionTranslation::class,
        \App\Models\PromotionRule::class,
        \App\Models\PromotionUserGroup::class,
        
        // 文章相关
        \App\Models\Article::class,
        \App\Models\ArticleTranslation::class,
        \App\Models\EditorUpload::class,
        
        // 用户相关
        \App\Models\User::class,
        \App\Models\UserGroup::class,
        \App\Models\UserGroupTranslation::class,
        \App\Models\Address::class,
        
        // 基础数据
        \App\Models\Currency::class,
        \App\Models\Language::class,
        \App\Models\Country::class,
        \App\Models\CountryTranslation::class,
        \App\Models\Zone::class,
        \App\Models\ZoneTranslation::class,
        
        // 其他
        \App\Models\Contact::class,
        \App\Models\Manager::class,
        
        // 媒体文件（图片、资源等）
        \App\Models\Media::class,
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
    // 默认10分钟，适应跨国网络延迟和大文件传输
    'timeout' => env('SYNC_TIMEOUT', 600),
    
    // 媒体文件下载超时时间（秒）
    // 默认15分钟，适应大文件传输和跨国网络延迟
    'media_download_timeout' => env('SYNC_MEDIA_DOWNLOAD_TIMEOUT', 900),
    
    // 批量同步配置
    // 批量同步时每批处理的记录数（建议50-100，根据网络情况调整）
    'batch_sync_size' => env('SYNC_BATCH_SYNC_SIZE', 50),
];
