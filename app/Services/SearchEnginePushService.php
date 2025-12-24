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
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                // 'Authorization' => 'Bearer ' . config('services.bing.api_key')
            ])->post(config('services.bing.api') . '?apikey=' . config('services.bing.api_key'), [
                'siteUrl' => config('services.bing.site'),
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
                'Authorization' => 'Bearer ' . config('services.google.api_key')
            ])->post(config('services.google.api'), [
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
