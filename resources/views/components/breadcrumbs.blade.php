@props([
    'items' => [],
    'homeLabel' => __('app.home'),
    'homeUrl' => locaRoute('home'),
])

<nav class="py-9" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center space-x-2 text-sm text-gray-500">
        <li>
            <a href="{{ $homeUrl }}" class="hover:text-green-700 font-medium flex items-center">
                <x-heroicon-o-home class="w-4 h-4 mr-1" />{{ $homeLabel }}
            </a>
        </li>
        @foreach($items as $item)
            <li>
                <span class="mx-2">/</span>
            </li>
            <li>
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-green-700 font-medium">{{ $item['label'] }}</a>
                @else
                    <span class="text-green-700 font-semibold">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
