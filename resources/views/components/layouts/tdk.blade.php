@props(['title' => '', 'description' => false, 'keywords' => false])

<title>{{ $title }}-{{ config('app.name') }}</title>
<meta name="title" content="{{ $title }}">

@if($description)
<meta name="description" content="{{ $description }}">
@endif

@if($keywords)
<meta name="keywords" content="{{ $keywords }}">
@endif