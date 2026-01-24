@props([
    'items' => [],
    'homeLabel' => __('app.home'),
    'homeUrl' => locaRoute('home'),
])

<nav class="p-5 my-9 bg-gray-50 rounded-xl" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center space-x-2 text-sm text-gray-500">
        <li>
            <a href="{{ $homeUrl }}" class="hover:text-teal-700 font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l9-9 9 9M5 10v10h4v-6h6v6h4V10"/>
                </svg>
                {{ $homeLabel }}
            </a>
        </li>
        @foreach($items as $item)
            <li>
                <span class="mx-2">/</span>
            </li>
            <li>
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-teal-700 font-medium">{{ $item['label'] }}</a>
                @else
                    <span class="text-teal-700 font-semibold">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
