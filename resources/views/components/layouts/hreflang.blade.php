@php
    $service = app(\App\Services\LocaleCurrencyService::class);
    $languages = $service->getLanguages();
    $defaultCode = $service->getDefaultLanguageCode();
@endphp
@if ($languages->isNotEmpty())
@foreach ($languages as $lang)
    @php
        $code = $lang->code;
        $hreflang = str_replace('_', '-', $code);
        $url = switch_locale_url($code);
    @endphp
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $url }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ switch_locale_url($defaultCode) }}">
@endif
