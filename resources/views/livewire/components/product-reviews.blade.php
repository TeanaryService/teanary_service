<div class="rating">
    <div class="mb-6">
        @if (session('review_submitted'))
            <div class="bg-teal-100 text-teal-800 px-4 py-2 rounded mb-4">
                {{ session('review_submitted') }}
            </div>
        @endif

        @auth
            <form wire:submit.prevent="submit" class="mb-6 bg-gray-50 rounded-lg p-6 shadow">
                <x-widgets.form-container>
                    <div class="flex items-center gap-2 mb-4">
                    <label class="font-semibold mr-2">{{ __('home.rating') }}</label>
                    <div class="flex items-center gap-1" x-data="{ rating: @entangle('rating') }">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <x-widgets.radio 
                                    wire="rating"
                                    :value="$i"
                                    class="hidden"
                                    x-model="rating"
                                />
                                <svg @click="rating = {{ $i }}"
                                    class="w-6 h-6 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                    :class="{
                                        'text-yellow-400': rating >= {{ $i }},
                                        'text-gray-300': rating <
                                            {{ $i }}
                                    }"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.921-.755 1.688-1.54 1.118l-3.38-2.455a1 1 0 00-1.175 0l-3.38 2.455c-.784.57-1.838-.197-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.049 9.393c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.966z" />
                                </svg>
                            </label>
                        @endfor

                        <span class="ml-2 text-sm text-gray-500" x-text="rating + ' / 5'">{{ $rating }} / 5</span>
                    </div>

                </div>
                <x-widgets.form-field>
                    <x-widgets.textarea 
                        wire="content"
                        rows="3"
                        placeholder="{{ __('home.review_placeholder') }}"
                    />
                </x-widgets.form-field>
                    <x-widgets.button 
                        type="submit"
                        class="w-full px-6 py-2 font-bold"
                    >
                        {{ __('home.submit_review') }}
                    </x-widgets.button>
                </x-widgets.form-container>
            </form>
        @else
            <div class="mb-4 0 text-white rounded-lg p-6 shadow flex flex-col items-center gap gap-2 bg-teal-500">
                <a href="{{ locaRoute('auth.login') }}" wire:navigate
                    class="font-semibold">{{ __('app.login') }}</a>
                <a href="{{ locaRoute('auth.register') }}" wire:navigate
                    class="font-semibold">{{ __('app.register') }}</a>
                <span class="text-gray-50">{{ __('home.login_to_review') }}</span>
            </div>
        @endauth
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4">{{ __('home.product_reviews') }}</h3>
        @forelse($reviews as $review)
            <div class="border-b py-4 border-teal-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-teal-700">{{ $review->user->name ?? __('home.anonymous') }}</span>
                    <span class="flex items-center">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $review->rating >= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.921-.755 1.688-1.54 1.118l-3.38-2.455a1 1 0 00-1.175 0l-3.38 2.455c-.784.57-1.838-.197-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.049 9.393c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.966z" />
                            </svg>
                        @endfor
                    </span>
                    @if ($review->productVariant)
                        <span class="text-gray-500 text-xs ml-2">
                            {{ $this->getProductVariantSpecs($review->productVariant, $lang) }}
                        </span>
                    @endif
                </div>
                <div class="text-gray-800 mb-1">{{ $review->content }}</div>
                <div class="text-gray-400 text-xs">{{ $review->created_at->format('Y-m-d H:i') }}</div>
            </div>
        @empty
            <x-widgets.empty-state 
                icon="heroicon-o-chat-bubble-left-right"
                :title="__('home.no_reviews')"
                class="py-8"
            />
        @endforelse

        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
