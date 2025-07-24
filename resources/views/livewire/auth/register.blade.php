<div class="max-w-7xl mx-auto px-4 py-10 flex justify-between">
    <div></div>
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">注册</h2>
        <form wire:submit.prevent="register">
            <input type="text" wire:model.defer="name" placeholder="昵称"
                class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
            <input type="email" wire:model.defer="email" placeholder="邮箱"
                class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
            <input type="password" wire:model.defer="password" placeholder="密码"
                class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
            <input type="password" wire:model.defer="password_confirmation" placeholder="确认密码"
                class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />

            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
            <button type="submit"
                class="w-full bg-teal-600 text-white font-medium px-8 py-3 rounded-lg hover:bg-teal-700 transition-colors mb-2">注册</button>
        </form>
        <div class="flex justify-between mt-4">
            <a href="{{ locaRoute('auth.login') }}" class="text-teal-600 hover:underline">已有账号？登录</a>
        </div>
    </div>
</div>
