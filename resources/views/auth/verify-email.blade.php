<x-layouts.app>
    <div class="min-h-[40vh] flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    {{ __('auth.verify_email') }}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    {{ __('auth.verify_email_message') }}
                </p>
            </div>
            
            @if (session()->has('message'))
                <div class="rounded-md bg-teal-50 p-4">
                    <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                </div>
            @endif
            
            <div class="mt-8 space-y-6">
                <form method="POST" action="{{ route('verification.send', ['locale' => app()->getLocale()]) }}">
                    @csrf
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        {{ __('auth.resend_verification_email') }}
                    </button>
                </form>
                
                <div class="text-center">
                    <a href="{{ locaRoute('home') }}" class="text-sm text-teal-600 hover:text-teal-500">
                        {{ __('app.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
