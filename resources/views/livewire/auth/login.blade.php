<div class="max-w-7xl mx-auto px-4 py-10 flex justify-between">
    <div></div>
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">登录</h2>
        <form wire:submit.prevent="login">
            <input type="email" wire:model="email" placeholder="邮箱"
                class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror

            <input type="password" wire:model="password" placeholder="密码"
                class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror

            <!-- 记住我 -->
            <label class="inline-flex items-center mb-4">
                <input type="checkbox" wire:model="remember" class="form-checkbox h-5 w-5 text-teal-600">
                <span class="ml-2 text-gray-700">记住我</span>
            </label>

            <button type="submit"
                class="w-full bg-teal-600 text-white font-medium px-8 py-3 rounded-lg hover:bg-teal-700 transition-colors mb-2">
                登录
            </button>
        </form>
        <div class="flex justify-between mt-4">
            <a href="{{ locaRoute('auth.register') }}" class="text-teal-600 hover:underline">注册</a>
            <a href="{{ locaRoute('auth.forgot-password') }}" class="text-teal-600 hover:underline">忘记密码</a>
        </div>
    </div>
</div>
