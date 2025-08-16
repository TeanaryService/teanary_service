<?php

namespace Deployer;

require 'recipe/laravel.php';

// 配置
set('repository', 'git@gitee.com:new-cms/teanary_service.git');
set('keep_releases', 2);
set('default_stage', 'production');

// 共享和可写
add('shared_files', [
    'public/sitemap.xml'
]);
add('shared_dirs', []);
add('writable_dirs', ['storage', 'bootstrap/cache']);

// 主机配置
host('teanary')
    ->set('hostname', '107.174.127.181')
    ->set('port', 22)
    ->set('remote_user', 'root')
    ->setIdentityFile('~/.ssh/vpn')
    ->set('deploy_path', '/home/wwwroot/teanary')
    ->set('branch', 'main')
    ->set('http_user', 'www');

host('local')
    ->set('hostname', '192.168.1.143')
    ->set('port', 22)
    ->set('remote_user', 'xcalder')
    ->setIdentityFile('~/.ssh/teanary_local')
    ->set('deploy_path', '/home/wwwroot/teanary.test')
    ->set('branch', 'main')
    ->set('http_user', 'www');

// ⏬ 自定义任务

desc('构建前端资源');
task('npm:build', function () {
    within('{{release_or_current_path}}', function () {
        run('rm -f package-lock.json');
        run('npm install');
        run('npm run build');
        run('rm -rf node_modules tests printer_server');
        run('mv public/vendor/livewire public/');
    });
});

desc('运行系统测试');
task('artisan:app:test', function () {
    within('{{release_or_current_path}}', function () {
        run('php artisan app:test');
    });
});

desc('Filament 缓存优化');
task('artisan:filament:optimize', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan filament:optimize');
    run('{{bin/php}} {{release_or_current_path}}/artisan filament:cache-components');
});

desc('刷新scout/缓存');
task('artisan:sync:pull', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan sync:pull');
});

desc('重载系统服务');
task('system:reload', function () {
    run('sudo supervisorctl reload');
    run('sudo lnmp reload');
})->once(); // 每个部署只执行一次（一次 per node）

// ⏬ Hook 任务顺序

after('deploy:vendors', 'npm:build');
after('deploy:symlink', 'system:reload');
after('deploy:symlink', 'artisan:optimize');
after('deploy:symlink', 'artisan:filament:optimize');
after('deploy:failed', 'deploy:unlock');
after('deploy:failed', 'deploy:rollback');
