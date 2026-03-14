<div class="space-y-6">
    <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('home.article.base_name') }}</h3>
    <div class="grid gap-6 {{ is_array($class) ? implode(' ', $class) : $class }}">
        @foreach ($articles as $article)
            <x-widgets.article-item :article="$article" size="small" />
        @endforeach
    </div>
</div>
