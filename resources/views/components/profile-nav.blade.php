<div class="w-full mx-auto bg-white rounded-xl shadow-md overflow-hidden">
    <ul class="divide-y divide-gray-200">
        <li>
            <a href="{{ locaRoute('user.orders') }}"
                class="flex items-center gap-3 px-5 py-4 text-sm font-medium text-gray-700 transition hover:bg-teal-50 hover:text-teal-700">
                <x-heroicon-o-shopping-bag class="w-5 h-5 text-teal-600" />
                {{ __('orders.my_orders') }}
            </a>
        </li>
        <li>
            <a href="{{ locaRoute('user.profile') }}"
                class="flex items-center gap-3 px-5 py-4 text-sm font-medium text-gray-700 transition hover:bg-teal-50 hover:text-teal-700">
                <x-heroicon-o-user class="w-5 h-5 text-teal-600" />
                {{ __('app.profile') }}
            </a>
        </li>
        <li>
            <a href="{{ locaRoute('user.addresses') }}"
                class="flex items-center gap-3 px-5 py-4 text-sm font-medium text-gray-700 transition hover:bg-teal-50 hover:text-teal-700">
                <x-heroicon-o-map-pin class="w-5 h-5 text-teal-600" />
                {{ __('app.addresses.my_addresses') }}
            </a>
        </li>
    </ul>
</div>