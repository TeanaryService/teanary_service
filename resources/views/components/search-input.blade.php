<!-- 搜索组件 -->
<div x-data="{ open: false }" class="relative flex-1 max-w-10 md:max-w-lg mx-0 md:mx-12">

    <!-- 移动端：搜索按钮 -->
    <div class="md:hidden relative">
        <button @click="open = !open" class="text-gray-600 p-2" type="button">
            <x-heroicon-o-magnifying-glass class="w-6 h-6" />
        </button>

        <!-- 悬浮搜索框 -->
        <div x-show="open" x-transition @click.away="open = false"
            class="absolute left-0 mt-2 w-screen max-w-xs z-50 bg-white border border-gray-200 shadow-lg rounded-lg p-3">
            <form method="GET" action="{{ locaRoute('search') }}">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('search.placeholder') }}"
                        class="w-full px-4 py-3 pl-10 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 桌面端：正常搜索框 -->
    <form method="GET" action="{{ locaRoute('search') }}" class="hidden md:block w-full">
        <div class="relative">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ __('search.placeholder') }}"
                class="w-full px-4 py-3 pl-12 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
            </button>
        </div>
    </form>
</div>
