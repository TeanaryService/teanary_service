<div class="rating">
    <div class="mb-6">
        @if(session('review_submitted'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('review_submitted') }}
            </div>
        @endif

        @auth
            <form wire:submit.prevent="submit" class="mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <label class="font-semibold">{{ __('home.rating') }}</label>
                    <select wire:model="rating" class="border rounded px-2 py-1">
                        @for($i=5; $i>=1; $i--)
                            <option value="{{ $i }}">{{ $i }}★</option>
                        @endfor
                    </select>
                </div>
                <div class="mb-2">
                    <textarea wire:model="content" rows="3" class="w-full border rounded px-3 py-2" placeholder="{{ __('home.review_placeholder') }}"></textarea>
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-bold hover:bg-green-700">
                    {{ __('home.submit_review') }}
                </button>
            </form>
        @else
            <div class="mb-4">
                <a href="{{ route('filament.personal.auth.login') }}" class="text-green-600 font-semibold mr-4">{{ __('app.login') }}</a>
                <a href="{{ route('filament.personal.auth.register') }}" class="text-green-600 font-semibold">{{ __('app.register') }}</a>
                <span class="text-gray-500 ml-2">{{ __('home.login_to_review') }}</span>
            </div>
        @endauth
    </div>

    <div>
        <h3 class="text-lg font-bold mb-4">{{ __('home.product_reviews') }}</h3>
        @forelse($reviews as $review)
            <div class="border-b py-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-green-700">{{ $review->user->name ?? __('home.anonymous') }}</span>
                    <span class="text-yellow-500">{{ str_repeat('★', $review->rating) }}</span>
                    @if($review->productVariant)
                        <span class="text-gray-500 text-xs ml-2">
                            @php
                                $specs = $review->productVariant->specificationValues->map(function ($sv) use ($lang) {
                                    $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                    return $trans && $trans->name ? $trans->name : $sv->id;
                                })->implode(' / ');
                            @endphp
                            {{ $specs }}
                        </span>
                    @endif
                </div>
                <div class="text-gray-800 mb-1">{{ $review->content }}</div>
                <div class="text-gray-400 text-xs">{{ $review->created_at->format('Y-m-d H:i') }}</div>
            </div>
        @empty
            <div class="text-gray-500 py-8 text-center">{{ __('home.no_reviews') }}</div>
        @endforelse

        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
