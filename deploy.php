<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('keep_releases', 2);
set('repository', 'git@gitee.com:new-cms/kmflora_service.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts
host('flower')
    ->set('hostname', '107.174.127.181')
    ->set('port', '22')
    ->set('http_user', 'www')
    ->set('branch', 'main')
    ->set('remote_user', 'root')
    ->setIdentityFile('~/.ssh/vpn')
    ->set('deploy_path', '/home/wwwroot/flower');

// Hooks
desc('laravel resource build');
task('npm:build', function () {
    cd('{{release_or_current_path}}');
    run('rm package-lock.json && npm install && npm run build && rm -rf node_modules tests printer_server && mv public/vendor/livewire public/');
});

desc('run test');
task('artisan:app:test', function () {
    cd('{{release_or_current_path}}');
    run('php artisan app:test');
});

desc('Cache the framework bootstrap files');
task('artisan:filament:optimize', artisan('filament:optimize'));

// 开发环境可以 fresh + seed
task('artisan:migrate:fresh:seed', function () {
    cd('{{release_or_current_path}}');
    run('sudo -u www php artisan migrate:fresh --seed');
});

desc('system reload');
task('system:reload', function () {
    run('sudo supervisorctl reload');
    run('sudo lnmp php-fpm reload');
})->oncePerNode();

// Events
after('deploy:vendors', 'npm:build');

after('deploy:symlink', 'system:reload');

after('deploy:failed', 'deploy:unlock');

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'artisan:storage:link',
    'artisan:config:cache',
    // 'artisan:route:cache',
    'artisan:view:cache',
    'artisan:event:cache',
    'artisan:migrate',
    'deploy:publish',
]);