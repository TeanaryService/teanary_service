<?php

namespace App\Http\Middleware;

use App\Services\LocaleCurrencyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackTraffic
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            // 只统计前台访问，排除管理后台和个人中心
            if ($this->shouldTrack($request)) {
                $this->recordTraffic($request);
            }

            return $response;
        } catch (\Exception $e) {
            // 如果中间件链中出现异常，记录但不影响请求处理
            // 流量统计不应该影响正常的请求处理
            Log::warning('TrackTraffic middleware error: '.$e->getMessage(), [
                'path' => $request->path(),
                'exception' => $e,
            ]);

            // 重新抛出异常，让 Laravel 的异常处理器处理
            throw $e;
        }
    }

    /**
     * 判断是否应该统计此请求
     */
    protected function shouldTrack(Request $request): bool
    {
        $path = $request->path();
        $segments = explode('/', trim($path, '/'));

        // 排除管理后台（考虑 locale 前缀，如 en/manager/login）
        if (in_array('manager', $segments)) {
            return false;
        }

        // 排除个人中心（考虑 locale 前缀，如 en/login）
        // 检查是否是用户中心相关的路径
        $userCenterPaths = ['login', 'register', 'profile', 'orders', 'addresses', 'notifications', 'forgot-password', 'reset-password'];
        if (count($segments) >= 2 && in_array($segments[1], $userCenterPaths)) {
            return false;
        }

        // 排除 API 路由
        if (str_starts_with($path, 'api')) {
            return false;
        }

        // 排除静态资源
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }

        // 只统计 GET 请求
        if ($request->method() !== 'GET') {
            return false;
        }

        return true;
    }

    /**
     * 记录流量数据到缓存.
     */
    protected function recordTraffic(Request $request): void
    {
        try {
            // 获取当前时间，精确到分钟
            $statDate = now()->startOfMinute();
            $path = $request->path();
            $method = $request->method();
            $ip = $request->ip();
            $userAgent = $request->userAgent();
            $referer = $request->header('referer');

            // 安全获取 locale，避免在中间件链早期阶段出错
            $locale = $request->segment(1);
            if (! $locale || ! in_array($locale, $this->getSupportedLocales())) {
                $locale = app()->getLocale() ?: 'en';
            }

            $isBot = $this->isBot($userAgent);
            $spiderSource = $isBot ? $this->getSpiderSource($userAgent) : null;

            // 生成缓存键（基于时间、路径、方法、IP、是否爬虫、爬虫来源）
            $cacheKey = $this->generateCacheKey($statDate, $path, $method, $ip, $isBot, $spiderSource);

            // 使用原子操作增加计数
            Cache::increment($cacheKey);

            // 将流量数据添加到待写入队列
            $this->addToWriteQueue($statDate, $path, $method, $ip, $userAgent, $referer, $locale, $isBot, $spiderSource);
        } catch (\Exception $e) {
            // 记录流量统计错误，但不影响请求处理
            Log::warning('TrackTraffic recordTraffic error: '.$e->getMessage(), [
                'path' => $request->path(),
                'exception' => $e,
            ]);
        }
    }

    /**
     * 获取支持的语言列表.
     */
    protected function getSupportedLocales(): array
    {
        try {
            $service = app(LocaleCurrencyService::class);

            return $service->getLanguages()->pluck('code')->toArray() ?: ['en'];
        } catch (\Exception $e) {
            // 如果服务不可用，返回默认语言
            return ['en'];
        }
    }

    /**
     * 判断是否为爬虫.
     */
    protected function isBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true; // 没有 User-Agent 的视为爬虫
        }

        $userAgent = strtolower($userAgent);

        // 常见爬虫关键词
        $botPatterns = [
            // 搜索引擎爬虫
            'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
            'sogou', 'exabot', 'facebot', 'ia_archiver', 'msnbot', 'ahrefsbot',

            // 社交媒体爬虫
            'facebookexternalhit', 'twitterbot', 'linkedinbot', 'pinterest',

            // 其他爬虫
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python-requests',
            'java/', 'go-http-client', 'httpclient', 'apache-httpclient',
            'scrapy', 'mechanize', 'phantomjs', 'headless', 'selenium',

            // 监控和工具
            'uptimerobot', 'pingdom', 'monitor', 'check', 'validator',
            'feed', 'rss', 'reader', 'aggregator',

            // 恶意爬虫
            'semrush', 'majestic', 'dotbot', 'ahrefs', 'mj12bot',
        ];

        foreach ($botPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取爬虫来源.
     */
    protected function getSpiderSource(?string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        // 搜索引擎爬虫
        $spiderSources = [
            'google' => ['googlebot', 'google'],
            'bing' => ['bingbot', 'msnbot', 'bing'],
            'baidu' => ['baiduspider', 'baidu'],
            'yandex' => ['yandexbot', 'yandex'],
            'yahoo' => ['slurp', 'yahoo'],
            'duckduckgo' => ['duckduckbot', 'duckduckgo'],
            'sogou' => ['sogou', 'sogou web spider'],
            'facebook' => ['facebookexternalhit', 'facebook'],
            'twitter' => ['twitterbot', 'twitter'],
            'linkedin' => ['linkedinbot', 'linkedin'],
            'pinterest' => ['pinterest', 'pinterestbot'],
            'semrush' => ['semrushbot', 'semrush'],
            'ahrefs' => ['ahrefsbot', 'ahrefs'],
            'majestic' => ['mj12bot', 'majestic'],
            'dotbot' => ['dotbot'],
            'exabot' => ['exabot'],
            'ia_archiver' => ['ia_archiver', 'archive.org'],
        ];

        foreach ($spiderSources as $source => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($userAgent, $pattern)) {
                    return $source;
                }
            }
        }

        // 如果包含bot、crawler、spider等通用关键词，但无法识别具体来源
        if (preg_match('/\b(bot|crawler|spider|scraper)\b/', $userAgent)) {
            return 'other';
        }

        return 'unknown';
    }

    /**
     * 生成缓存键.
     */
    protected function generateCacheKey($statDate, $path, $method, $ip, bool $isBot, ?string $spiderSource = null): string
    {
        $minute = $statDate->format('Y-m-d H:i');
        $botFlag = $isBot ? 'bot' : 'human';
        $source = $spiderSource ?? 'none';

        return "traffic:count:{$minute}:{$path}:{$method}:{$botFlag}:{$source}:".md5($ip);
    }

    /**
     * 将流量数据添加到待写入队列.
     */
    protected function addToWriteQueue($statDate, $path, $method, $ip, $userAgent, $referer, $locale, bool $isBot, ?string $spiderSource = null): void
    {
        $queueKey = 'traffic:queue:'.$statDate->format('Y-m-d-H-i');

        // 生成唯一键（基于时间、路径、方法、IP、是否爬虫、爬虫来源）
        $uniqueKey = md5("{$statDate->format('Y-m-d H:i')}:{$path}:{$method}:".md5($ip).":{$isBot}:".($spiderSource ?? ''));

        // 获取或创建队列
        $queue = Cache::get($queueKey, []);

        // 如果已存在，更新计数；否则添加新记录
        if (isset($queue[$uniqueKey])) {
            ++$queue[$uniqueKey]['count'];
        } else {
            $queue[$uniqueKey] = [
                'stat_date' => $statDate->format('Y-m-d H:i:s'),
                'path' => $path,
                'method' => $method,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'locale' => $locale,
                'is_bot' => $isBot,
                'spider_source' => $spiderSource,
                'count' => 1,
            ];
        }

        // 保存队列（设置较长的过期时间，确保任务能处理）
        Cache::put($queueKey, $queue, now()->addHours(1));
    }
}
