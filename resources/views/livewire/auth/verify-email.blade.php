<div class="max-w-7xl mx-auto px-6">
    <x-auth-center>
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
            <h2 class="text-2xl font-bold mb-4 text-green-600">{{ __('auth.email_verified_title') }}</h2>
            <p>{{ __('auth.email_verified_message') }}</p>
        </div>
    </x-auth-center>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('auth.email_verified_title') }}" />

    <script>
        setTimeout(() => {
            window.location.href = "{{ locaRoute('home') }}";
        }, 2000);
    </script>
@endPushOnce
