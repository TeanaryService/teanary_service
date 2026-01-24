<?php

/**
 * Teanary 项目 Deployer 部署配置
 * 
 * 使用方法：
 * 1. 复制此文件到项目根目录：cp docs/example.deploy.php deploy.php
 * 2. 修改下面的配置（仓库地址、服务器信息等）
 * 3. 运行部署：./bin/dep deploy production
 */

namespace Deployer;

require 'recipe/laravel.php';

// ============================================
// 基础配置
// ============================================

// Git 仓库地址（修改为你的仓库地址）
set('repository', 'git@gitee.com:teanary/teanary_service.git');

// 保留的发布版本数量（用于回滚）
set('keep_releases', 3);

// Shared files and directories
add('shared_files', [
    '.env',
    'public/sitemap.xml',
]);

add('shared_dirs', [
    'storage',
]);

add('writable_dirs', [
    'storage',
    'bootstrap/cache',
    'public',
]);

// ============================================
// 服务器配置
// ============================================
// 修改以下配置为你的服务器信息

host('production')  // 服务器名称（可以改为 production、staging 等）
    ->set('hostname', '192.168.1.1')  // 服务器 IP 地址
    ->set('port', 22)  // SSH 端口（默认 22）
    ->set('remote_user', 'deployer')  // SSH 用户名
    ->setIdentityFile('~/.ssh/id_rsa')  // SSH 密钥路径（推荐使用 ~/.ssh/id_rsa）
    ->set('deploy_path', '/home/wwwroot/teanary')  // 部署路径
    ->set('branch', 'main')  // 部署分支（main 或 master）
    ->set('http_user', 'www')  // Web 服务器用户（通常是 www 或 www-data）
    ->set('php_fpm_service', 'php-fpm.service')  // PHP-FPM 服务名
    ->set('supervisor_service', 'supervisor.service');  // Supervisor 服务名

// 可以添加多个服务器
// host('staging')
//     ->set('hostname', '192.168.1.2')
//     ->set('remote_user', 'deployer')
//     ->setIdentityFile('~/.ssh/id_rsa')
//     ->set('deploy_path', '/home/wwwroot/teanary-staging')
//     ->set('branch', 'dev');

// Tasks

// NPM 安装和构建任务 - 使用内置方法
task('npm:install', function () {
    run('cd {{release_path}} && npm ci --prefer-offline --no-audit');
})->desc('安装 NPM 依赖');

task('npm:build', function () {
    run('cd {{release_path}} && npm run build');
})->desc('构建前端资源');

// 清理无关代码和文件
task('cleanup:unnecessary', function () {
    $files = [
        'deploy.yaml',
        'example-deploy.yaml',
        'sync_storage.sh',
        'README.md',
        'RELEASE.md',
        'SYNC.md',
        'LICENSE',
        'phpstan.neon',
        'phpunit.xml',
        'pint.json',
        '.cursorignore',
        '.editorconfig',
        '.env.example',
        '.gitattributes',
        '.gitignore',
    ];

    $dirs = [
        'bin',
        'tests',
        'deployment',
        '.phpunit.cache',
        'storage/coverage',
        'storage/phpstan',
        'node_modules',
        'tests',
    ];

    run('cd {{release_path}} && rm -f '.implode(' ', $files));
    run('cd {{release_path}} && rm -rf '.implode(' ', $dirs));
})->desc('清理无关代码和文件');

// 重启 PHP-FPM
task('php-fpm:restart', function () {
    $service = get('php_fpm_service');
    run("sudo systemctl restart $service");
})->desc('重启 PHP-FPM');

// 重启 Supervisor
task('supervisor:restart', function () {
    run('sudo supervisorctl reread');
    run('sudo supervisorctl update');
    run('sudo supervisorctl restart all');
})->desc('重启 Supervisor');

// 组合任务:重启服务
task('services:restart', [
    'php-fpm:restart',
    'supervisor:restart',
])->desc('重启 PHP-FPM 和 Supervisor');

// 组合任务:完整的 NPM 构建流程
task('npm:build:full', [
    'npm:install',
    'npm:build',
])->desc('完整的 NPM 构建流程');

// 组合任务:发布后清理和优化
task('deploy:post-publish', [
    'cleanup:unnecessary',
])->desc('发布后清理和优化');

// ============================================
// Hooks
// ============================================

// 在安装 Composer 依赖后执行 NPM 构建
after('deploy:vendors', 'npm:build:full');

// 注意：Laravel recipe 会自动运行数据库迁移（deploy:migrate）
// 首次部署后，需要手动运行填充数据：
// php artisan db:seed --force

// 在发布前清理缓存
before('deploy:publish', function () {
    run('{{bin/php}} {{release_path}}/artisan config:clear');
    run('{{bin/php}} {{release_path}}/artisan cache:clear');
});

// 在发布后执行清理和优化
after('deploy:publish', 'deploy:post-publish');

// 部署成功后重启服务
after('deploy:success', 'services:restart');

// 部署失败时解锁
after('deploy:failed', 'deploy:unlock');
