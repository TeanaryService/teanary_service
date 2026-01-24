import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                // 只监听必要的文件，减少内存占用
                'resources/views/**/*.blade.php',
                'app/Livewire/**/*.php',
                'routes/**/*.php',
            ],
        }),
        tailwindcss(),
    ],
    // 优化构建配置以减少内存占用
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: undefined, // 禁用手动分块以减少内存使用
            },
        },
    },
    // 优化开发服务器配置
    server: {
        watch: {
            // 减少监听的文件数量
            ignored: [
                '**/node_modules/**',
                '**/.git/**',
                '**/storage/**',
                '**/vendor/**',
                '**/tests/**',
                '**/docs/**',
                '**/database/**',
                '**/config/**',
                '**/lang/**',
                '**/app/Models/**',
                '**/app/Services/**',
                '**/app/Utils/**',
                '**/app/Http/**',
                '**/app/Jobs/**',
                '**/app/Notifications/**',
                '**/app/Support/**',
                '**/app/Traits/**',
            ],
        },
    },
    // 优化依赖预构建
    optimizeDeps: {
        include: ['axios'],
        exclude: [],
    },
});
