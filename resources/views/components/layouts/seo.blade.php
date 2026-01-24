@props([
    'title' => '',
    'description' => false,
    'keywords' => false,
    'image' => null, // 新增图片参数
])

@php
    $metaImage = getSeoMetaImage($image);
    
    // 解码 HTML 实体，避免双重编码（如 &amp;amp;#039; -> '）
    // 循环解码直到没有更多变化，最多 3 次
    $decodeHtmlEntities = function($text) {
        if (empty($text)) {
            return $text;
        }
        $decoded = $text;
        $maxAttempts = 3;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $newDecoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($newDecoded === $decoded) {
                break; // 没有更多需要解码的内容
            }
            $decoded = $newDecoded;
        }
        return $decoded;
    };
    
    $decodedTitle = $decodeHtmlEntities($title);
    $decodedDescription = $description ? $decodeHtmlEntities($description) : false;
    $decodedKeywords = $keywords ? $decodeHtmlEntities($keywords) : false;
@endphp

<title>{{ $decodedTitle }} - {{ config('app.name') }}</title>
<meta name="title" content="{{ $decodedTitle }}">

@if($decodedDescription)
<meta name="description" content="{{ $decodedDescription }}">
@endif

@if($decodedKeywords)
<meta name="keywords" content="{{ $decodedKeywords }}">
@endif

{{-- Open Graph --}}
<meta property="og:title" content="{{ $decodedTitle }}">
@if($decodedDescription)
<meta property="og:description" content="{{ $decodedDescription }}">
@endif
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $metaImage }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $decodedTitle }}">
@if($decodedDescription)
<meta name="twitter:description" content="{{ $decodedDescription }}">
@endif
<meta name="twitter:image" content="{{ $metaImage }}">
<meta name="twitter:site" content="{{ config('app.name') }}">

{{-- AI/LLM SEO hints --}}
@if($decodedKeywords)
<meta name="ai-seo-keywords" content="{{ $decodedKeywords }}">
@endif
@if($decodedDescription)
<meta name="ai-seo-summary" content="{{ $decodedDescription }}">
@endif
<meta name="ai-seo-title" content="{{ $decodedTitle }}">