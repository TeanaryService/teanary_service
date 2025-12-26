<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TranslationService
{
    protected string $appId;

    protected string $secretKey;

    protected string $apiUrl = 'https://fanyi-api.baidu.com/api/trans/vip/translate';

    public function __construct()
    {
        $tokens = [
            [
                'app_id' => '20180720000187144',
                'secret_key' => 'Ht60Z8iDm7GuIhuiIX47',
            ],
        ];
        // 随机选择一个 token
        $token = $tokens[array_rand($tokens)];

        $this->appId = $token['app_id'];
        $this->secretKey = $token['secret_key'];
    }

    /**
     * 翻译文本
     *
     * @param  string  $query  要翻译的文本
     * @param  string  $from  原语言，auto 表示自动识别
     * @param  string  $to  目标语言（如 zh、en、jp 等）
     * @return string|null 翻译结果，失败返回 null
     */
    public function translate(string $query, string $from = 'auto', string $to = 'en'): ?string
    {
        $from = $this->mapLocaleToBaiduLangCode($from);
        $to = $this->mapLocaleToBaiduLangCode($to);

        $salt = time();
        $sign = md5($this->appId.$query.$salt.$this->secretKey);

        $response = Http::get($this->apiUrl, [
            'q' => $query,
            'from' => $from,
            'to' => $to,
            'appid' => $this->appId,
            'salt' => $salt,
            'sign' => $sign,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['trans_result'][0]['dst'])) {
                return $data['trans_result'][0]['dst'];
            }
        }

        return null;
    }

    public function mapLocaleToBaiduLangCode(string $locale): string
    {
        $map = [
            'auto' => 'auto',

            // 中文简体
            'zh' => 'zh',
            'zh_cn' => 'zh',
            'zh-hans' => 'zh',

            // 中文繁体
            'zh_tw' => 'cht',
            'zh-hant' => 'cht',

            // 粤语
            'yue' => 'yue',

            // 文言文
            'wyw' => 'wyw',

            // 英语
            'en' => 'en',
            'en_us' => 'en',
            'en_gb' => 'en',

            // 日语
            'ja' => 'jp',
            'ja_jp' => 'jp',
            'jp' => 'jp',

            // 韩语
            'ko' => 'kor',
            'ko_kr' => 'kor',
            'kor' => 'kor',

            // 法语
            'fr' => 'fra',
            'fr_fr' => 'fra',

            // 西班牙语
            'es' => 'spa',
            'es_es' => 'spa',

            // 德语
            'de' => 'de',
            'de_de' => 'de',

            // 意大利语
            'it' => 'it',
            'it_it' => 'it',

            // 葡萄牙语
            'pt' => 'pt',
            'pt_pt' => 'pt',
            'pt_br' => 'pt',

            // 俄语
            'ru' => 'ru',
            'ru_ru' => 'ru',

            // 阿拉伯语
            'ar' => 'ara',
            'ar_sa' => 'ara',

            // 泰语
            'th' => 'th',
            'th_th' => 'th',

            // 荷兰语
            'nl' => 'nl',
            'nl_nl' => 'nl',

            // 其他常见语种（同上表）
            'pl' => 'pl',
            'cs' => 'cs',
            'slo' => 'slo',
            'bul' => 'bul',
            'el' => 'el',
            'est' => 'est',
            'dan' => 'dan',
            'fin' => 'fin',
            'rom' => 'rom',
            'swe' => 'swe',
            'hu' => 'hu',
            'vie' => 'vie',
        ];

        $locale = strtolower(str_replace('-', '_', trim($locale)));

        return $map[$locale] ?? 'auto';
    }
}
