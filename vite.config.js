import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                // Blade 模板文件
                'resources/views/**/*.blade.php',
                // Livewire 组件
                'app/Livewire/**/*.php',
                // 路由文件
                'routes/**/*.php',
                // 配置文件
                'config/**/*.php',
                // 语言文件
                'lang/**/*.php',
                // App 目录下的 PHP 文件（模型、控制器、服务等）
                'app/**/*.php',
            ],
        }),
        tailwindcss(),
    ],
});
