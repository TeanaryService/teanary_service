<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SearchEnginePushService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SearchEnginePushServiceTest extends TestCase
{
    protected SearchEnginePushService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SearchEnginePushService();
        Log::shouldReceive('warning')->byDefault();
        Log::shouldReceive('error')->byDefault();
    }

    public function test_push_returns_empty_array_when_services_disabled(): void
    {
        Config::set('services.bing.enabled', false);
        Config::set('services.google.enabled', false);

        $result = $this->service->push('https://example.com/page');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_push_to_bing_when_enabled(): void
    {
        Config::set('services.bing.enabled', true);
        Config::set('services.bing.api_key', 'test-key');
        Config::set('services.bing.api', 'https://api.bing.com/test');
        Config::set('services.bing.site', 'https://example.com');
        Config::set('services.google.enabled', false);

        Http::fake([
            'api.bing.com/*' => Http::response(['status' => 'success'], 200),
        ]);

        $result = $this->service->push('https://example.com/page');

        $this->assertArrayHasKey('bing', $result);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.bing.com/test?apikey=test-key' &&
                   $request->method() === 'POST';
        });
    }

    public function test_push_to_bing_returns_false_when_missing_config(): void
    {
        Config::set('services.bing.enabled', true);
        Config::set('services.bing.api_key', null);
        Config::set('services.bing.api', 'https://api.bing.com/test');
        Config::set('services.bing.site', 'https://example.com');

        $result = $this->service->push('https://example.com/page');

        $this->assertArrayHasKey('bing', $result);
        $this->assertFalse($result['bing']);
    }

    public function test_push_to_bing_returns_false_on_failed_response(): void
    {
        Config::set('services.bing.enabled', true);
        Config::set('services.bing.api_key', 'test-key');
        Config::set('services.bing.api', 'https://api.bing.com/test');
        Config::set('services.bing.site', 'https://example.com');

        Http::fake([
            'api.bing.com/*' => Http::response(['error' => 'Failed'], 500),
        ]);

        $result = $this->service->push('https://example.com/page');

        $this->assertArrayHasKey('bing', $result);
        $this->assertFalse($result['bing']);
    }

    public function test_push_to_google_when_enabled(): void
    {
        Config::set('services.bing.enabled', false);
        Config::set('services.google.enabled', true);
        Config::set('services.google.api_key', 'test-key');
        Config::set('services.google.api', 'https://api.google.com/test');

        Http::fake([
            'api.google.com/*' => Http::response(['status' => 'success'], 200),
        ]);

        $result = $this->service->push('https://example.com/page');

        $this->assertArrayHasKey('google', $result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.google.com') &&
                   $request->method() === 'POST' &&
                   $request->hasHeader('Authorization', 'Bearer test-key');
        });
    }

    public function test_push_to_google_returns_false_when_missing_config(): void
    {
        Config::set('services.google.enabled', true);
        Config::set('services.google.api_key', null);
        Config::set('services.google.api', 'https://api.google.com/test');

        $result = $this->service->push('https://example.com/page');

        $this->assertArrayHasKey('google', $result);
        $this->assertFalse($result['google']);
    }

    public function test_push_to_google_returns_false_on_failed_response(): void
    {
        Config::set('services.google.enabled', true);
        Config::set('services.google.api_key', 'test-key');
        Config::set('services.google.api', 'https://api.google.com/test');

        Http::fake([
            'api.google.com/*' => Http::response(['error' => 'Failed'], 401),
        ]);

        $result = $this->service->push('https://example.com/page');

        $this->assertArrayHasKey('google', $result);
        $this->assertFalse($result['google']);
    }

    public function test_push_handles_exception_gracefully(): void
    {
        Config::set('services.bing.enabled', true);
        Config::set('services.bing.api_key', 'test-key');
        Config::set('services.bing.api', 'https://api.bing.com/test');
        Config::set('services.bing.site', 'https://example.com');

        Http::fake(function () {
            throw new \Exception('Network error');
        });

        $result = $this->service->push('https://example.com/page');

        $this->assertArrayHasKey('bing', $result);
        $this->assertFalse($result['bing']);
    }
}

