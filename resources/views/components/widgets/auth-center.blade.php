<div class="relative flex items-center justify-center min-h-[60vh] bg-teal-50">
    <!-- 背景图层 -->
    <div class="absolute inset-0 bg-cover bg-no-repeat"
        style="background-image: url('{{ asset('images/auth-bg.jpg') }}'); opacity: 0.1;"></div>

    <!-- 内容层 -->
    <div class="relative z-10">
        {{ $slot }}
    </div>
</div>
