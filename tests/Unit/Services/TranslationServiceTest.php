<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Http;

class TranslationServiceTest extends TestCase
{
    protected TranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TranslationService();
    }

    public function test_translate_returns_translated_text_on_success(): void
    {
        Http::fake([
            'fanyi-api.baidu.com/*' => Http::response([
                'trans_result' => [
                    [
                        'dst' => 'Hello'
                    ]
                ]
            ], 200),
        ]);

        $result = $this->service->translate('你好', 'zh', 'en');

        $this->assertEquals('Hello', $result);
    }

    public function test_translate_returns_null_on_failure(): void
    {
        Http::fake([
            'fanyi-api.baidu.com/*' => Http::response(['error' => 'Invalid request'], 400),
        ]);

        $result = $this->service->translate('你好', 'zh', 'en');

        $this->assertNull($result);
    }

    public function test_translate_returns_null_when_no_trans_result(): void
    {
        Http::fake([
            'fanyi-api.baidu.com/*' => Http::response([], 200),
        ]);

        $result = $this->service->translate('你好', 'zh', 'en');

        $this->assertNull($result);
    }

    public function test_map_locale_to_baidu_lang_code(): void
    {
        $this->assertEquals('zh', $this->service->mapLocaleToBaiduLangCode('zh'));
        $this->assertEquals('en', $this->service->mapLocaleToBaiduLangCode('en'));
        $this->assertEquals('jp', $this->service->mapLocaleToBaiduLangCode('ja'));
        $this->assertEquals('auto', $this->service->mapLocaleToBaiduLangCode('unknown'));
    }
}

