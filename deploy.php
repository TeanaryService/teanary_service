<?php

namespace Deployer;

require 'recipe/laravel.php';

// ============================================
// 基础配置
// ============================================
set('repository', 'git@gitee.com:teanary/teanary_service.git');
set('keep_releases', 3);
set('default_stage', 'production');

// 共享文件和目录
add('shared_files', [
    'public/sitemap.xml',
]);

add('shared_dirs', []);

add('writable_dirs', [
    'storage',
    'bootstrap/cache',
    'public',
]);

// ============================================
// 主机配置
// ============================================
host('teanary-online')
    ->set('hostname', '107.174.127.181')
    ->set('port', 22)
    ->set('remote_user', 'root')
    ->setIdentityFile('~/.ssh/vpn')
    ->set('deploy_path', '/home/wwwroot/teanary')
    ->set('branch', 'dev')
    ->set('http_user', 'www');

host('teanary-sync')
    ->set('hostname', 'chatterup.fun')
    ->set('port', 2022)
    ->set('remote_user', 'xcalder')
    ->setIdentityFile('~/.ssh/pi5')
    ->set('deploy_path', '/home/wwwroot/teanary')
    ->set('branch', 'dev')
    ->set('http_user', 'www');

// ============================================
// 前端构建任务
// ============================================
desc('构建前端资源');
task('npm:build', function () {
    cd('{{release_path}}');
    run('npm ci --prefer-offline --no-audit');
    run('npm run build');
    run('rm -rf node_modules tests printer_server');
    run('mv public/vendor/livewire public/ 2>/dev/null || true');
});

// ============================================
// Artisan 任务
// ============================================
desc('Filament 缓存优化');
task('artisan:filament:optimize', function () {
    cd('{{release_path}}');
    run('{{bin/php}} artisan filament:optimize');
    run('{{bin/php}} artisan filament:cache-components');
});

// ============================================
// PHP-FCGI 任务
// ============================================
desc('重启 PHP-FCGI 服务');
task('php-fcgi:restart', function () {
    writeln('<info>正在重启 PHP-FCGI 服务...</info>');
    run('sudo /etc/init.d/php-fpm restart');
});

// ============================================
// 队列任务（由 Supervisor 管理）
// ============================================
desc('重启队列服务');
task('queue:restart', function () {
    writeln('<info>正在重启队列服务...</info>');
    run('sudo supervisorctl restart teanary-queue:*');
});

desc('检查队列状态');
task('queue:status', function () {
    run('sudo supervisorctl status teanary-queue:*');
});

desc('检查所有服务状态');
task('supervisor:status', function () {
    run('sudo supervisorctl status all');
});

// ============================================
// 配置部署任务（仅在需要时手动执行）
// ============================================
desc('部署 Supervisor 配置');
task('supervisor:deploy', function () {
    run('sudo cp {{release_path}}/deployment/supervisor-queue.conf /etc/supervisor/conf.d/teanary-queue.conf');
    run('sudo supervisorctl reread');
    run('sudo supervisorctl update');
    run('sudo supervisorctl start teanary-queue:* 2>&1 || true');
});

desc('部署 Nginx 配置');
task('nginx:deploy', function () {
    run('sudo cp {{release_path}}/deployment/nginx-teanary-phpfcgi.conf /usr/local/nginx/conf/vhost/teanary.com.conf');
    run('sudo nginx -t');
    run('sudo lnmp reload');
});

desc('更新配置 - 重新部署 Nginx 和 Supervisor');
task('deploy:config', [
    'nginx:deploy',
    'supervisor:deploy',
]);

// ============================================
// 部署流程 Hook（使用 Laravel recipe 标准流程）
// ============================================
// 前端构建在 vendors 之后
after('deploy:vendors', 'npm:build');

before('deploy:symlink', 'artisan:cache:clear');
before('deploy:symlink', 'artisan:filament:optimize');

// symlink 之后重启服务
after('deploy:symlink', 'php-fcgi:restart');
after('deploy:symlink', 'queue:restart');

// 失败处理
after('deploy:failed', 'deploy:unlock');
