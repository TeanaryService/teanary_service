<div class="max-w-xl mx-auto py-16 text-center">
    <h2 class="text-2xl font-bold mb-4 text-green-600">{{ __('auth.email_verified_title') }}</h2>
    <p>{{ __('auth.email_verified_message') }}</p>

    <script>
        setTimeout(() => {
            window.location.href = "{{ locaRoute('home') }}";
        }, 2000);
    </script>
</div>
