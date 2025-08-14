<div class="space-y-6">
    <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('article.base_name') }}</h3>
    
    @foreach ($articles as $article)
        @php
            $translation = $article->articleTranslations->first();
            $title = $translation?->title ?? 'Untitled';
            $image = $article->getFirstMediaUrl('image', 'thumb');
        @endphp

        <div class="bg-gray-50 hover:bg-white transition duration-300 p-3 rounded-lg shadow-sm border border-gray-200 flex gap-4 items-start">
            @if ($image)
                <div class="w-24 h-16 flex-shrink-0 overflow-hidden rounded-md">
                    <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}">
                        <img src="{{ $image }}" alt="{{ $title }}" class="object-cover w-full h-full">
                    </a>
                </div>
            @endif

            <div class="flex-1">
                <h4 class="text-base font-medium text-gray-900 line-clamp-2">
                    <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}" 
                       class="hover:underline hover:text-teal-700">
                        {{ $title }}
                    </a>
                </h4>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $article->created_at->format('Y-m-d') }}
                </p>
            </div>
        </div>
    @endforeach
</div>