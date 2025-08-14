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
        if (config('search_engine.bing.enabled')) {
            $results['bing'] = $this->pushToBing($url);
        }
        
        // Google推送
        if (config('search_engine.google.enabled')) {
            $results['google'] = $this->pushToGoogle($url);
        }
        
        return $results;
    }
    
    protected function pushToBing(string $url)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('search_engine.bing.api_key')
            ])->post(config('search_engine.bing.api'), [
                'siteUrl' => config('search_engine.bing.site'),
                'url' => $url
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Bing push failed: ' . $e->getMessage());
            return false;
        }
    }
    
    protected function pushToGoogle(string $url)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('search_engine.google.api_key')
            ])->post(config('search_engine.google.api'), [
                'url' => $url,
                'type' => 'URL_UPDATED'
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Google push failed: ' . $e->getMessage());
            return false;
        }
    }
}
