<?php

namespace App\Models;

use App\Traits\HasSnowflakeId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 流量统计模型
 *
 * @property int $id
 * @property string $path 访问路径
 * @property string $method HTTP方法
 * @property string|null $ip 访问IP
 * @property string|null $user_agent 用户代理
 * @property string|null $referer 来源页面
 * @property string|null $locale 语言代码
 * @property bool $is_bot 是否为爬虫
 * @property string|null $spider_source 爬虫来源（如google、bing等）
 * @property int $count 访问次数（同一分钟内相同路径的访问次数）
 * @property \Carbon\Carbon $stat_date 统计日期（精确到分钟）
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class TrafficStatistic extends Model
{
    use HasFactory, HasSnowflakeId;

    protected $fillable = [
        'path',
        'method',
        'ip',
        'user_agent',
        'referer',
        'locale',
        'is_bot',
        'spider_source',
        'count',
        'stat_date',
    ];

    protected $casts = [
        'stat_date' => 'datetime',
        'count' => 'int',
        'is_bot' => 'boolean',
    ];

    /**
     * 获取指定时间范围内的统计数据
     */
    public static function getStatsByDateRange($startDate, $endDate, ?bool $isBot = null)
    {
        $query = static::whereBetween('stat_date', [$startDate, $endDate]);
        
        if ($isBot !== null) {
            $query->where('is_bot', $isBot);
        }
        
        return $query->selectRaw('
                DATE(stat_date) as date,
                HOUR(stat_date) as hour,
                COUNT(*) as page_views,
                SUM(count) as total_visits,
                COUNT(DISTINCT path) as unique_pages,
                COUNT(DISTINCT ip) as unique_ips
            ')
            ->groupBy('date', 'hour')
            ->orderBy('date')
            ->orderBy('hour')
            ->get();
    }

    /**
     * 获取热门页面
     */
    public static function getTopPages($startDate, $endDate, $limit = 10, ?bool $isBot = null)
    {
        $query = static::whereBetween('stat_date', [$startDate, $endDate]);
        
        if ($isBot !== null) {
            $query->where('is_bot', $isBot);
        }
        
        return $query->selectRaw('path, SUM(count) as total_visits, COUNT(*) as page_views')
            ->groupBy('path')
            ->orderByDesc('total_visits')
            ->limit($limit)
            ->get();
    }
}
