<div class="max-w-7xl mx-auto px-4 py-10 min-h-screen bg-white space-y-6">
    @foreach ($articles as $article)
        @php
            $translation = $article->articleTranslations->first();
        @endphp

        <div class="border-b pb-4">
            <h2 class="text-xl font-semibold">
                <a href="{{ locaRoute('article.show', ['slug' => $article->slug]) }}">
                    {{ $translation?->title ?? 'No title' }}
                </a>
            </h2>
            <p class="text-gray-600">{{ $translation?->summary ?? '' }}</p>
        </div>
    @endforeach

    <div>
        {{ $articles->links() }}
    </div>
</div>
