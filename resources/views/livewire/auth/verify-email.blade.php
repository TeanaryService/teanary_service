<div class="max-w-xl mx-auto py-16 text-center">
    <h2 class="text-2xl font-bold mb-4 text-green-600">邮箱验证成功！</h2>
    <p>我们已经验证了你的邮箱，正在跳转到首页...</p>

    <script>
        setTimeout(() => {
            window.location.href = "{{ route('home', ['locale' => app()->getLocale()]) }}";
        }, 2000);
    </script>
</div>
