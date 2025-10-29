<?php

namespace Deployer;

require 'recipe/laravel.php';

// 配置
set('repository', 'git@gitee.com:new-cms/teanary_service.git');
set('keep_releases', 2);
set('default_stage', 'production');

// 共享和可写
add('shared_files', [
    'public/sitemap.xml',
    'frankenphp'
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

// host('local')
//     ->set('hostname', '192.168.1.143')
//     ->set('port', 22)
//     ->set('remote_user', 'xcalder')
//     ->setIdentityFile('~/.ssh/teanary')
//     ->set('deploy_path', '/home/wwwroot/teanary.test')
//     ->set('branch', 'main')
//     ->set('http_user', 'www');

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

desc('检查 Octane 环境');
task('octane:check', function () {
    within('{{release_or_current_path}}', function () {
        run('php artisan octane:status');
    });
});

desc('启动 Octane 服务');
task('octane:start', function () {
    within('{{release_or_current_path}}', function () {
        run('php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000 --workers=4 --max-requests=500 --watch');
    });
});

desc('重启 Octane 服务');
task('octane:reload', function () {
    within('{{release_or_current_path}}', function () {
        run('php artisan octane:reload --server=frankenphp');
    });
});

desc('停止 Octane 服务');
task('octane:stop', function () {
    within('{{release_or_current_path}}', function () {
        run('php artisan octane:stop --server=frankenphp');
    });
});

desc('优化 Octane 配置');
task('octane:optimize', function () {
    within('{{release_or_current_path}}', function () {
        run('php artisan config:cache');
        run('php artisan route:cache');
        run('php artisan view:cache');
        run('php artisan event:cache');
    });
});

desc('部署 Supervisor 配置');
task('supervisor:deploy', function () {
    run('sudo cp {{release_or_current_path}}/deployment/supervisor-octane.conf /etc/supervisor/conf.d/octane.conf');
    run('sudo cp {{release_or_current_path}}/deployment/supervisor-queue.conf /etc/supervisor/conf.d/teanary-queue.conf');
    run('sudo supervisorctl reread');
    run('sudo supervisorctl update');
    run('sudo supervisorctl start octane:*');
    run('sudo supervisorctl start teanary-queue:*');
});

desc('部署 Nginx 配置');
task('nginx:deploy', function () {
    run('sudo cp {{release_or_current_path}}/deployment/nginx-teanary-octane.conf /usr/local/nginx/conf/vhost/teanary.com.conf');
    run('sudo nginx -t');
    run('sudo lnmp reload');
});

desc('重启队列服务');
task('queue:restart', function () {
    run('sudo supervisorctl restart teanary-queue:*');
});

desc('检查队列状态');
task('queue:status', function () {
    run('sudo supervisorctl status teanary-queue:*');
});

desc('重载系统服务');
task('system:reload', function () {
    run('sudo supervisorctl reload');
    run('sudo lnmp reload');
})->once(); // 每个部署只执行一次（一次 per node）

// ⏬ Hook 任务顺序

after('deploy:vendors', 'npm:build');
after('deploy:symlink', 'nginx:deploy');
after('deploy:symlink', 'supervisor:deploy');
after('deploy:symlink', 'octane:optimize');
after('deploy:symlink', 'octane:reload');
after('deploy:symlink', 'artisan:optimize');
after('deploy:symlink', 'artisan:filament:optimize');
after('deploy:symlink', 'system:reload');
after('deploy:failed', 'deploy:unlock');
