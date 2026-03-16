<?php

use App\Models\Address;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Contact;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Currency;
use App\Models\EditorUpload;
use App\Models\Language;
use App\Models\Manager;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipment;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\ProductReview;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductVariantSpecificationValue;
use App\Models\Promotion;
use App\Models\PromotionProductVariant;
use App\Models\PromotionRule;
use App\Models\PromotionTranslation;
use App\Models\PromotionUserGroup;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserGroupTranslation;
use App\Models\Zone;
use App\Models\ZoneTranslation;

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
        Product::class,
        ProductTranslation::class,
        ProductVariant::class,
        ProductReview::class,
        ProductCategory::class,
        ProductAttributeValue::class,

        // 分类相关
        Category::class,
        CategoryTranslation::class,

        // 属性相关
        Attribute::class,
        AttributeTranslation::class,
        AttributeValue::class,
        AttributeValueTranslation::class,

        // 规格相关
        Specification::class,
        SpecificationTranslation::class,
        SpecificationValue::class,
        SpecificationValueTranslation::class,
        ProductVariantSpecificationValue::class,

        // 订单相关
        Order::class,
        OrderItem::class,
        OrderShipment::class,

        // 购物车相关
        Cart::class,
        CartItem::class,

        // 促销相关
        Promotion::class,
        PromotionTranslation::class,
        PromotionRule::class,
        PromotionUserGroup::class,
        PromotionProductVariant::class,

        // 文章相关
        Article::class,
        ArticleTranslation::class,
        EditorUpload::class,

        // 用户相关
        User::class,
        UserGroup::class,
        UserGroupTranslation::class,
        Address::class,

        // 基础数据
        Currency::class,
        Language::class,
        Country::class,
        CountryTranslation::class,
        Zone::class,
        ZoneTranslation::class,

        // 其他
        Contact::class,
        Manager::class,

        // 媒体文件（图片、资源等）- 只同步表数据，不处理文件
        Media::class,
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
