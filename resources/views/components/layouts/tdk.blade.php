@props(['title' => '', 'description' => false, 'keywords' => false])

<title>{{ $title }} - {{ config('app.name') }}</title>
<meta name="title" content="{{ $title }}">

@if($description)
<meta name="description" content="{{ $description }}">
@endif

@if($keywords)
<meta name="keywords" content="{{ $keywords }}">
@endif

{{-- Open Graph --}}
<meta property="og:title" content="{{ $title }}">
@if($description)
<meta property="og:description" content="{{ $description }}">
@endif
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('logo.png') }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
@if($description)
<meta name="twitter:description" content="{{ $description }}">
@endif
<meta name="twitter:image" content="{{ asset('logo.png') }}">
<meta name="twitter:site" content="@{{ config('app.twitter', 'kmflora') }}">

{{-- AI/LLM SEO hints --}}
@if($keywords)
<meta name="ai-seo-keywords" content="{{ $keywords }}">
@endif
@if($description)
<meta name="ai-seo-summary" content="{{ $description }}">
@endif
<meta name="ai-seo-title" content="{{ $title }}">