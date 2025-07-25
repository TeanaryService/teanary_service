<div class="w-full max-w-sm mx-auto bg-white rounded-xl shadow-md overflow-hidden">
    <ul class="divide-y divide-gray-200">
        <li>
            <a href="{{ locaRoute('user.orders') }}"
                class="flex items-center gap-3 px-5 py-4 text-sm font-medium text-gray-700 transition hover:bg-teal-50 hover:text-teal-700">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7H19m-9-7v7m4-7v7" />
                </svg>
                {{ __('orders.my_orders') }}
            </a>
        </li>
        <li>
            <a href="{{ locaRoute('user.profile') }}"
                class="flex items-center gap-3 px-5 py-4 text-sm font-medium text-gray-700 transition hover:bg-teal-50 hover:text-teal-700">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                </svg>
                {{ __('app.profile') }}
            </a>
        </li>
        <li>
            <a href="{{ locaRoute('user.addresses') }}"
                class="flex items-center gap-3 px-5 py-4 text-sm font-medium text-gray-700 transition hover:bg-teal-50 hover:text-teal-700">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 2C8.134 2 5 5.134 5 9c0 4.25 7 13 7 13s7-8.75 7-13c0-3.866-3.134-7-7-7z" />
                    <circle cx="12" cy="9" r="2.5" />
                </svg>
                {{ __('addresses.my_addresses') }}
            </a>
        </li>
    </ul>
</div>
