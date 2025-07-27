<?php

/**
 * 双向同步数据配置文件
 */

return [

    // 这台机器需要从对方“拉取”的表（只拉这些表）
    'pull_from_remote' => array_filter(array_map('trim', explode(',', env('SYNC_PULL_FROM_REMOTE', '')))),

    // 对方机器的 API 地址（不需要 token 也行）
    'remote_url' => env('SYNC_REMOTE_URL', 'http://localhost:8000'),

    // 同步最近多少分钟内的数据
    'sync_interval_minutes' => (int) env('SYNC_INTERVAL_MINUTES', 10),
];