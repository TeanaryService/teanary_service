<div class="min-h-[40vh] bg-gray-50 font-chinese antialiased pt-0">
    <style>
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin-slow 20s linear infinite;
        }
    </style>
    <!-- Header -->
    <header
        class="bg-gradient-to-br from-teal-600 to-teal-700 py-16 md:py-20 text-center relative overflow-hidden shadow-lg">
        <!-- Background decoration -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full opacity-10"
                style="background-image: radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(0, 0, 0, 0.1) 0%, transparent 50%);">
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 md:px-8 relative z-10">
            <h1
                class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 tracking-tight relative z-10 drop-shadow-lg flex items-center justify-center gap-3 animate-bounce">
                <svg class="w-[1.2em] h-[1.2em] inline-block align-middle flex-shrink-0 animate-spin-slow" viewBox="0 0 1024 1024"
                    version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <path
                        d="M1002.315595 501.157647c0 276.781176-224.376471 501.157647-501.157647 501.157647C224.376772 1002.315294 0.000301 777.938824 0.000301 501.157647 0.000301 224.376471 224.376772 0 501.157948 0c276.781176 0 501.157647 224.376471 501.157647 501.157647"
                        fill="#4C86C6"></path>
                    <path
                        d="M1001.502419 473.328941h-89.690353a34.816 34.816 0 0 1 0-69.632h80.926118A498.537412 498.537412 0 0 0 897.717007 194.921412H609.942889C552.779595 194.921412 502.904772 238.953412 501.218184 296.116706a104.387765 104.387765 0 0 0 104.357647 107.580235h180.946823a34.846118 34.846118 0 0 1 0 69.632h-23.491765c-57.133176 0-106.977882 44.062118-108.724705 101.195294a104.417882 104.417882 0 0 0 104.387764 107.610353h209.79953a500.043294 500.043294 0 0 0 33.008941-208.805647"
                        fill="#31EC7C"></path>
                    <path
                        d="M501.097713 685.869176c-1.716706-57.133176-51.561412-101.195294-108.724706-101.195294H104.418184a34.816 34.816 0 1 1 0-69.571764h37.376c57.163294 0 107.038118-44.092235 108.724705-101.195294a104.417882 104.417882 0 0 0-104.357647-107.640471H39.303831A499.651765 499.651765 0 0 0 0.000301 501.157647c0 109.146353 34.996706 210.070588 94.208 292.352h302.531765c58.729412 0 106.134588-48.489412 104.357647-107.610353"
                        fill="#31EC7C"></path>
                    <path
                        d="M305.875125 39.454118A113.603765 113.603765 0 0 0 234.014419 13.914353H113.995595A114.025412 114.025412 0 0 0 0.000301 127.939765a114.025412 114.025412 0 0 0 76.318118 107.52 503.115294 503.115294 0 0 1 229.586823-196.005647"
                        fill="#B9CFDF"></path>
                    <path
                        d="M234.014419 241.935059a113.995294 113.995294 0 0 0 71.890823-202.480941 502.964706 502.964706 0 0 0-229.586823 195.975529c11.806118 4.156235 24.455529 6.505412 37.707294 6.505412H234.014419z"
                        fill="#1565B2"></path>
                    <path
                        d="M941.267125 740.954353a503.265882 503.265882 0 0 1-143.23953 163.870118h120.771765a83.486118 83.486118 0 0 0 22.467765-163.84"
                        fill="#B9CFDF"></path>
                    <path
                        d="M941.267125 740.954353a84.028235 84.028235 0 0 0-22.497883-3.132235h-264.493176a83.486118 83.486118 0 1 0 0 167.002353h143.751529a503.265882 503.265882 0 0 0 143.23953-163.84"
                        fill="#1565B2"></path>
                    <path
                        d="M772.608301 501.157647H424.598889a76.559059 76.559059 0 1 1 0-153.148235h348.009412a76.559059 76.559059 0 1 1 0 153.118117M404.570654 835.252706h-140.950588a54.814118 54.814118 0 0 1 0-109.628235h140.950588a54.844235 54.844235 0 0 1 0 109.628235"
                        fill="#FFFFFF"></path>
                </svg>
                <span class="animate-pulse">Teanary</span>
            </h1>
            <div class="w-20 h-1 bg-gradient-to-r from-transparent via-white/80 to-transparent rounded-full mx-auto mb-8 animate-pulse"></div>
            <p
                class="text-lg md:text-xl text-white/95 max-w-3xl mx-auto mt-8 relative z-10 leading-relaxed drop-shadow">
                🌍 {{ __('index.subtitle') }} 🚀
            </p>
            <div class="flex justify-center gap-3 flex-wrap mt-8 relative z-10">
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">✨ {{ __('index.badge_agpl') }}</span>
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">⚡ {{ __('index.badge_laravel') }}</span>
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">🌐 {{ __('index.badge_sync') }}</span>
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">🤖 {{ __('index.badge_ai') }}</span>
            </div>
        </div>
    </header>

    <!-- Sticky Navigation Bar -->
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-6 md:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center gap-1 text-sm text-gray-600">
                    <span class="animate-bounce">📍</span>
                    <span>{{ __('index.quick_nav') }}</span>
                </div>
                <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                    <a href="#tech-stack" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🚀 {{ __('index.nav_tech_stack') }}</a>
                    <a href="#features" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🌟 {{ __('index.nav_features') }}</a>
                    <a href="#demo" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🎮 {{ __('index.nav_demo') }}</a>
                    <a href="#pricing" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">💼 {{ __('index.nav_pricing') }}</a>
                    <a href="#license" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">📄 {{ __('index.nav_license') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 md:px-8 relative z-20">
        <!-- 技术栈 -->
        <div class="my-16" id="tech-stack">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center tracking-tight relative pb-4 mb-16">
                <span class="inline-block animate-spin-slow">🚀</span> {{ __('index.section_tech_stack') }}
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-purple-600 to-transparent rounded-full"></div>
            </h2>
            <div class="flex flex-wrap gap-3 justify-center mt-4">
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Laravel 12.x</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">PHP 8.1+</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">MySQL 8.0+</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">Redis</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Tailwind CSS 3.x</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">Livewire 3.x</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Livewire Manager Admin</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">Laravel Octane</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Ollama AI</div>
            </div>
        </div>

        <!-- 核心特性 -->
        <div class="my-16" id="features">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-16 text-center tracking-tight relative pb-4">
                <span class="inline-block animate-bounce">🌟</span> {{ __('index.section_features') }}
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-teal-600 to-transparent rounded-full"></div>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mt-4">
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-bounce">🌍</span> {{ __('index.feature_multi_node_title') }}
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_multi_node_1') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_multi_node_2') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_multi_node_3') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_multi_node_4') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_multi_node_5') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_multi_node_6') }}</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-pulse">🤖</span> {{ __('index.feature_ai_translation_title') }}
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ai_translation_1') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ai_translation_2') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ai_translation_3') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ai_translation_4') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ai_translation_5') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ai_translation_6') }}</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-bounce">🛒</span> {{ __('index.feature_chrome_plugin_title') }}
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_chrome_plugin_1') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_chrome_plugin_2') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_chrome_plugin_3') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_chrome_plugin_4') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_chrome_plugin_5') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_chrome_plugin_6') }}</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-pulse">🛍️</span> {{ __('index.feature_ecommerce_title') }}
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ecommerce_1') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ecommerce_2') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ecommerce_3') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ecommerce_4') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ecommerce_5') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_ecommerce_6') }}</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-bounce">🎨</span> {{ __('index.feature_admin_title') }}
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_admin_1') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_admin_2') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_admin_3') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_admin_4') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_admin_5') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_admin_6') }}</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-pulse">⚡</span> {{ __('index.feature_performance_title') }}
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_performance_1') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_performance_2') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_performance_3') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_performance_4') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_performance_5') }}</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.feature_performance_6') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 在线演示 -->
        <div class="my-16" id="demo">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center tracking-tight relative pb-4 mb-16">
                <span class="inline-block animate-pulse">🎮</span> {{ __('index.section_demo') }}
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-blue-600 to-transparent rounded-full"></div>
            </h2>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 md:p-8 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-center gap-6">
                    {{-- 按钮区域 --}}
                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-stretch sm:items-center w-full lg:w-auto">
                        <a href="https://demo.chatterup.fun:2003" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 px-4 sm:px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold text-sm sm:text-base transition-all duration-300 shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg border-none w-full sm:w-auto">
                            🚀 {{ __('index.demo_visit_frontend') }}
                        </a>
                        <a href="https://demo.chatterup.fun:2003/manager" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 px-4 sm:px-8 py-3 bg-white text-teal-600 no-underline rounded-md font-semibold text-sm sm:text-base transition-all duration-300 shadow-sm hover:bg-gray-50 hover:border-teal-700 border border-teal-600 w-full sm:w-auto">
                            ⚙️ {{ __('index.demo_visit_admin') }}
                        </a>
                    </div>
                    
                    {{-- 测试账号信息 --}}
                    <div class="w-full lg:w-auto lg:min-w-[280px]">
                        <div class="bg-white rounded-lg p-4 sm:p-5 text-left shadow-sm">
                            <h3 class="text-teal-600 mb-3 text-base sm:text-lg font-semibold">{{ __('index.demo_test_account') }}</h3>
                            <div class="text-gray-600 leading-relaxed text-xs sm:text-sm">
                                <div class="mb-2 break-words">
                                    <strong class="text-gray-900 font-semibold">{{ __('index.demo_email') }}:</strong> 
                                    <span class="break-all">demo@demo.com</span>
                                </div>
                                <div class="mb-2 break-words">
                                    <strong class="text-gray-900 font-semibold">{{ __('index.demo_password') }}:</strong> 
                                    <span class="break-all">demo123456</span>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200 text-gray-500 italic text-xs">{{ __('index.demo_account_note') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- 警告信息 --}}
                <div class="mt-5 text-gray-900 text-xs sm:text-sm leading-relaxed px-2 sm:px-6 md:px-10">
                    <p class="mb-1.5 break-words">⚠️ <strong>{{ __('index.demo_warning_title') }}:</strong> {{ __('index.demo_warning_1') }}</p>
                    <p class="mb-1.5">💻 {{ __('index.demo_warning_2') }}</p>
                    <p class="mb-1.5">🌐 {{ __('index.demo_warning_3') }}</p>
                    <p class="mb-1.5 break-words">📧 {{ __('index.demo_warning_4') }}<a href="mailto:hello@teanary.com"
                            class="text-teal-600 no-underline font-medium transition-colors hover:text-teal-700 hover:underline break-all">hello@teanary.com</a>
                    </p>
                </div>
            </div>
            <!-- 技术选择说明 -->
            <div>
                <h3 class="text-gray-900 my-16 text-xl font-semibold text-center">💡 {{ __('index.demo_why_sync_title') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 my-5">
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🌍 {{ __('index.demo_why_sync_1_title') }}</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">{{ __('index.demo_why_sync_1_desc') }}</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🔄 {{ __('index.demo_why_sync_2_title') }}</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">{{ __('index.demo_why_sync_2_desc') }}</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🛡️ {{ __('index.demo_why_sync_3_title') }}</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">{{ __('index.demo_why_sync_3_desc') }}</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🔐 {{ __('index.demo_why_sync_4_title') }}</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">{{ __('index.demo_why_sync_4_desc') }}</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">📁 {{ __('index.demo_why_sync_5_title') }}</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">{{ __('index.demo_why_sync_5_desc') }}</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🗄️ {{ __('index.demo_why_sync_6_title') }}</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">{{ __('index.demo_why_sync_6_desc') }}</p>
                    </div>
                </div>
                <div class="mt-6 p-4.5 bg-white rounded-lg text-center shadow-sm border border-gray-200">
                    <p class="text-gray-600 leading-relaxed text-sm"><strong
                            class="text-teal-600 font-semibold">{{ __('index.demo_why_sync_summary') }}</strong>
                    </p>
                </div>
            </div>
        </div>
        <!-- 商业服务 -->
        <div class="my-16" id="pricing">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 my-8 text-center tracking-tight relative">
                <span class="inline-block animate-pulse">💼</span> {{ __('index.section_pricing') }}
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-pink-600 to-transparent rounded-full"></div>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5 mt-4">
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🚀 {{ __('index.pricing_deployment_title') }}</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">
                        ¥500<small class="ml-2.5 text-sm text-gray-600 font-medium">{{ __('index.pricing_deployment_price') }}</small>
                    </div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_deployment_1') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_deployment_2') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_deployment_3') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_deployment_4') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_deployment_5') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_deployment_6') }}</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=部署服务咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">{{ __('index.pricing_deployment_consult') }}</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🔧 {{ __('index.pricing_maintenance_title') }}</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">
                        ¥1500<small class="ml-2.5 text-sm text-gray-600 font-medium">{{ __('index.pricing_maintenance_price') }}</small>
                    </div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_maintenance_1') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_maintenance_2') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_maintenance_3') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_maintenance_4') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_maintenance_5') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_maintenance_6') }}</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=维护服务咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">{{ __('index.pricing_maintenance_consult') }}</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🛒 {{ __('index.pricing_plugin_title') }}</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">¥1500</div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_plugin_1') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_plugin_2') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_plugin_3') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_plugin_4') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_plugin_5') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_plugin_6') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_plugin_7') }}</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=采集插件咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">{{ __('index.pricing_plugin_consult') }}</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🤖 {{ __('index.pricing_translation_title') }}</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">¥1500</div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_translation_1') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_translation_2') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_translation_3') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_translation_4') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_translation_5') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_translation_6') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_translation_7') }}</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=翻译端程序咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">{{ __('index.pricing_translation_consult') }}</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🎨 {{ __('index.pricing_custom_title') }}</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">
                        {{ __('index.pricing_custom_price') }}
                    </div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_custom_1') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_custom_2') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_custom_3') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_custom_4') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_custom_5') }}</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            {{ __('index.pricing_custom_6') }}</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=二次开发咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">{{ __('index.pricing_custom_consult') }}</a>
                </div>
            </div>
        </div>

        <!-- 开源协议 -->
        <div class="my-16" id="license">
            <h3 class="text-gray-900 mb-4 text-2xl font-semibold">
                <span class="inline-block animate-bounce">📄</span> {{ __('index.license_title') }}
            </h3>
            <p class="text-gray-600 mb-6 text-sm leading-relaxed">{{ __('index.license_desc', ['license' => 'AGPL-3.0']) }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
                <div
                    class="p-5 bg-white rounded-lg border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                    <h4 class="text-teal-600 mb-3 text-lg font-semibold">✅ {{ __('index.license_can_title') }}</h4>
                    <ul class="list-none p-0">
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_can_1') }}</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_can_2') }}</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_can_3') }}</li>
                    </ul>
                </div>
                <div
                    class="p-5 bg-white rounded-lg border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                    <h4 class="text-teal-600 mb-3 text-lg font-semibold">⚠️ {{ __('index.license_must_title') }}</h4>
                    <ul class="list-none p-0">
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_must_1') }}</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_must_2') }}</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_must_3') }}</li>
                    </ul>
                </div>
                <div
                    class="p-5 bg-white rounded-lg border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                    <h4 class="text-teal-600 mb-3 text-lg font-semibold">❌ {{ __('index.license_cannot_title') }}</h4>
                    <ul class="list-none p-0">
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_cannot_1') }}</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_cannot_2') }}</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            {{ __('index.license_cannot_3') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('index.seo_title') }}" description="{{ __('index.seo_description') }}" />
