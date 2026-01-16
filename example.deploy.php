<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@gitee.com:teanary/teanary_service.git');
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

// Hosts
host('demo')
    ->set('hostname', '192.168.1.1')
    ->set('port', 22)
    ->set('remote_user', 'deployer')
    ->setIdentityFile('~/.ssh/deployer.key')
    ->set('deploy_path', '/home/wwwroot/demo')
    ->set('branch', 'dev')
    ->set('http_user', 'www')
    ->set('php_fpm_service', 'php-fpm.service')
    ->set('supervisor_service', 'supervisor.service');

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
        'OPTIMIZATION.md',
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
    
    run('cd {{release_path}} && rm -f ' . implode(' ', $files));
    run('cd {{release_path}} && rm -rf ' . implode(' ', $dirs));
})->desc('清理无关代码和文件');

// Filament 优化任务
task('artisan:filament:optimize', function () {
    run('{{bin/php}} {{release_path}}/artisan filament:optimize');
    run('{{bin/php}} {{release_path}}/artisan filament:cache-components');
})->desc('优化 Filament');

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
    'artisan:filament:optimize',
])->desc('发布后清理和优化');

// Hooks

// 在安装 Composer 依赖后执行 NPM 构建
after('deploy:vendors', 'npm:build:full');

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