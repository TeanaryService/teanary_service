<div>
    @if ($show)
        <div class="fixed bottom-0 left-0 w-full z-50">
            <div
                class="max-w-3xl mx-auto bg-white border border-gray-200 shadow-lg rounded-lg p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex-1 text-gray-700 text-sm">
                    {{ __('app.cookie_message') }}
                </div>
                <div>
                    <button wire:click="accept"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition">
                        {{ __('app.cookie_accept') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
