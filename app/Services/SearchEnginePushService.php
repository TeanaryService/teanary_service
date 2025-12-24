<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchEnginePushService
{
    public function push(string $url)
    {
        $results = [];
        
        // Bing推送
        if (config('services.bing.enabled')) {
            $results['bing'] = $this->pushToBing($url);
        }
        
        // Google推送
        if (config('services.google.enabled')) {
            $results['google'] = $this->pushToGoogle($url);
        }
        
        return $results;
    }
    
    protected function pushToBing(string $url)
    {
        try {
            $apiKey = config('services.bing.api_key');
            $apiUrl = config('services.bing.api');
            $site = config('services.bing.site');

            if (!$apiKey || !$apiUrl || !$site) {
                Log::warning('Bing push skipped: Missing configuration');
                return false;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '?apikey=' . $apiKey, [
                'siteUrl' => $site,
                'url' => $url
            ]);
            
            if (!$response->successful()) {
                Log::error('Bing push failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Bing push failed: ' . $e->getMessage());
            return false;
        }
    }
    
    protected function pushToGoogle(string $url)
    {
        try {
            $apiKey = config('services.google.api_key');
            $apiUrl = config('services.google.api');

            if (!$apiKey || !$apiUrl) {
                Log::warning('Google push skipped: Missing configuration');
                return false;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ])->post($apiUrl, [
                'url' => $url,
                'type' => 'URL_UPDATED'
            ]);
            
            if (!$response->successful()) {
                Log::error('Google push failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Google push failed: ' . $e->getMessage());
            return false;
        }
    }
}
