<div class="max-w-3xl mx-auto py-10 px-4">
    <h2 class="text-2xl font-bold mb-6">个人中心</h2>

    @if (session()->has('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="updateProfile" class="space-y-6 bg-white p-6 rounded-xl shadow-md">
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">昵称</label>
            <input type="text" wire:model.defer="name"
                class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200" />
            @error('name')
                <span class="text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">邮箱（不可修改）</label>
            <input type="email" value="{{ $email }}" disabled
                class="w-full px-4 py-2 border rounded-lg bg-gray-100 cursor-not-allowed" />
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">新密码（留空则不修改）</label>
            <input type="password" wire:model.defer="password"
                class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200" />
            @error('password')
                <span class="text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">确认新密码</label>
            <input type="password" wire:model.defer="password_confirmation"
                class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200" />
        </div>

        <button type="submit"
            class="w-full bg-teal-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-teal-700 transition-colors">
            保存修改
        </button>
    </form>
</div>
