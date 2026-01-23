<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-teal-50/30 font-chinese antialiased">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(20, 184, 166, 0.3); }
            50% { box-shadow: 0 0 40px rgba(20, 184, 166, 0.6); }
        }
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-glow {
            animation: glow 3s ease-in-out infinite;
        }
        .animate-spin-slow {
            animation: spin-slow 20s linear infinite;
        }
        .gradient-text {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 50%, #0f766e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section - 更吸引人的首屏 -->
    <header class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-teal-600 via-emerald-500 to-cyan-400">
        <!-- 动态背景装饰 -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-20">
                <div class="absolute top-20 left-20 w-72 h-72 bg-white rounded-full blur-3xl animate-float"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 bg-cyan-300 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>
                <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-emerald-300 rounded-full blur-3xl animate-float" style="animation-delay: 4s;"></div>
            </div>
            <!-- 网格背景 -->
            <div class="absolute inset-0" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
        </div>

        <div class="relative z-10 w-full max-w-screen 2xl:max-w-[80vw]  mx-auto px-6 md:px-8 py-20 text-center">
            <!-- Logo 和标题 -->
            <div class="mb-8 animate-float">
                <svg class="w-24 h-24 md:w-32 md:h-32 mx-auto mb-6 animate-spin-slow drop-shadow-2xl" viewBox="0 0 1024 1024"
                    version="1.1" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1002.315595 501.157647c0 276.781176-224.376471 501.157647-501.157647 501.157647C224.376772 1002.315294 0.000301 777.938824 0.000301 501.157647 0.000301 224.376471 224.376772 0 501.157948 0c276.781176 0 501.157647 224.376471 501.157647 501.157647" fill="#FFFFFF"></path>
                    <path d="M1001.502419 473.328941h-89.690353a34.816 34.816 0 0 1 0-69.632h80.926118A498.537412 498.537412 0 0 0 897.717007 194.921412H609.942889C552.779595 194.921412 502.904772 238.953412 501.218184 296.116706a104.387765 104.387765 0 0 0 104.357647 107.580235h180.946823a34.846118 34.846118 0 0 1 0 69.632h-23.491765c-57.133176 0-106.977882 44.062118-108.724705 101.195294a104.417882 104.417882 0 0 0 104.387764 107.610353h209.79953a500.043294 500.043294 0 0 0 33.008941-208.805647" fill="#31EC7C"></path>
                    <path d="M501.097713 685.869176c-1.716706-57.133176-51.561412-101.195294-108.724706-101.195294H104.418184a34.816 34.816 0 1 1 0-69.571764h37.376c57.163294 0 107.038118-44.092235 108.724705-101.195294a104.417882 104.417882 0 0 0-104.357647-107.640471H39.303831A499.651765 499.651765 0 0 0 0.000301 501.157647c0 109.146353 34.996706 210.070588 94.208 292.352h302.531765c58.729412 0 106.134588-48.489412 104.357647-107.610353" fill="#31EC7C"></path>
                    <path d="M305.875125 39.454118A113.603765 113.603765 0 0 0 234.014419 13.914353H113.995595A114.025412 114.025412 0 0 0 0.000301 127.939765a114.025412 114.025412 0 0 0 76.318118 107.52 503.115294 503.115294 0 0 1 229.586823-196.005647" fill="#B9CFDF"></path>
                    <path d="M234.014419 241.935059a113.995294 113.995294 0 0 0 71.890823-202.480941 502.964706 502.964706 0 0 0-229.586823 195.975529c11.806118 4.156235 24.455529 6.505412 37.707294 6.505412H234.014419z" fill="#1565B2"></path>
                    <path d="M941.267125 740.954353a503.265882 503.265882 0 0 1-143.23953 163.870118h120.771765a83.486118 83.486118 0 0 0 22.467765-163.84" fill="#B9CFDF"></path>
                    <path d="M941.267125 740.954353a84.028235 84.028235 0 0 0-22.497883-3.132235h-264.493176a83.486118 83.486118 0 1 0 0 167.002353h143.751529a503.265882 503.265882 0 0 0 143.23953-163.84" fill="#1565B2"></path>
                    <path d="M772.608301 501.157647H424.598889a76.559059 76.559059 0 1 1 0-153.148235h348.009412a76.559059 76.559059 0 1 1 0 153.118117M404.570654 835.252706h-140.950588a54.814118 54.814118 0 0 1 0-109.628235h140.950588a54.844235 54.844235 0 0 1 0 109.628235" fill="#FFFFFF"></path>
                </svg>
            </div>

            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-6 tracking-tight drop-shadow-2xl">
                <span class="inline-block animate-pulse">Teanary</span>
            </h1>
            
            <div class="w-32 h-1.5 bg-gradient-to-r from-transparent via-white/90 to-transparent rounded-full mx-auto mb-8 animate-pulse"></div>
            
            <p class="text-xl md:text-2xl lg:text-3xl text-white/95 max-w-4xl mx-auto mb-12 leading-relaxed drop-shadow-lg font-medium">
                🌍 {{ __('index.subtitle') }} 🚀
            </p>

            <!-- 核心卖点标签 -->
            <div class="flex flex-wrap justify-center gap-4 mb-12">
                <span class="bg-white/20 backdrop-blur-md text-white px-6 py-3 rounded-full text-sm md:text-base font-bold border-2 border-white/40 transition-all duration-300 shadow-xl hover:scale-110 hover:bg-white/30 hover:shadow-2xl transform animate-glow">
                    ✨ {{ __('index.badge_agpl') }}
                </span>
                <span class="bg-white/20 backdrop-blur-md text-white px-6 py-3 rounded-full text-sm md:text-base font-bold border-2 border-white/40 transition-all duration-300 shadow-xl hover:scale-110 hover:bg-white/30 hover:shadow-2xl transform" style="animation-delay: 0.1s;">
                    ⚡ {{ __('index.badge_laravel') }}
                </span>
                <span class="bg-white/20 backdrop-blur-md text-white px-6 py-3 rounded-full text-sm md:text-base font-bold border-2 border-white/40 transition-all duration-300 shadow-xl hover:scale-110 hover:bg-white/30 hover:shadow-2xl transform" style="animation-delay: 0.2s;">
                    🌐 {{ __('index.badge_sync') }}
                </span>
                <span class="bg-white/20 backdrop-blur-md text-white px-6 py-3 rounded-full text-sm md:text-base font-bold border-2 border-white/40 transition-all duration-300 shadow-xl hover:scale-110 hover:bg-white/30 hover:shadow-2xl transform" style="animation-delay: 0.3s;">
                    🤖 {{ __('index.badge_ai') }}
                </span>
            </div>

            <!-- CTA 按钮 -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="#demo" class="group relative px-8 py-4 bg-white text-teal-600 rounded-full font-bold text-lg shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 transform overflow-hidden">
                    <span class="relative z-10 flex items-center gap-2">
                        🎮 {{ __('index.nav_demo') }}
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-400 to-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>
                <a href="https://github.com/TeanaryService/teanary_srvice" target="_blank" rel="noopener noreferrer" class="group px-8 py-4 bg-white/10 backdrop-blur-md text-white border-2 border-white/40 rounded-full font-bold text-lg hover:bg-white/20 transition-all duration-300 hover:scale-110 transform">
                    <span class="flex items-center gap-2">
                        ⭐ GitHub
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>

        <!-- 滚动提示 -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </header>

    <!-- Sticky Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-gray-200/60 shadow-lg h-20">
        <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span class="animate-bounce">📍</span>
                    <span class="font-medium">{{ __('index.quick_nav') }}</span>
                </div>
                <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                    <a href="#tech-stack" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🚀 {{ __('index.nav_tech_stack') }}</a>
                    <a href="#features" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🌟 {{ __('index.nav_features') }}</a>
                    <a href="#demo" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🎮 {{ __('index.nav_demo') }}</a>
                    <a href="#pricing" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">💼 {{ __('index.nav_pricing') }}</a>
                    <a href="#license" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">📄 {{ __('index.nav_license') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8 py-16">
        <!-- 技术栈 - 更现代的设计 -->
        <section class="py-20" id="tech-stack">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black gradient-text mb-4 tracking-tight">
                    <span class="inline-block animate-bounce">🚀</span> {{ __('index.section_tech_stack') }}
                </h2>
                <p class="text-gray-600 text-lg md:text-xl max-w-2xl mx-auto">
                    基于最新技术栈构建，性能卓越，开发体验极佳
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @php
                $techStack = [
                    [
                        'name' => 'Laravel 12.x',
                        'color' => 'from-red-500 to-red-600',
                        'iconColor' => 'text-red-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.42l-4.428 4.428-1.572-1.572L21.428 12l-3.428-3.428 1.572-1.572L24 12.42zm-4.428-4.428L12.42 3.572l-1.572 1.572L12 7.428l3.428-3.428zm-7.144 0L5.144 3.572 3.572 5.144 7 8.572l3.428-3.428zm-7.144 7.144l-1.572-1.572L0 12.42l4.428 4.428 1.572-1.572L2.572 12l3.428-3.428zm7.144 0L12.42 20.428l1.572 1.572L21.428 12l-3.428-3.428-1.572 1.572L18.856 12l-3.428 3.428z"/></svg>'
                    ],
                    [
                        'name' => 'PHP 8.2+',
                        'color' => 'from-blue-500 to-blue-600',
                        'iconColor' => 'text-blue-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M13.1 3L17.9 8H13.1V3M13.1 21V16H17.9L13.1 21M4.2 8H8.2L12 3L8.2 8H4.2M8.2 16H4.2L12 21L8.2 16M19.8 8H15.8L12 3L15.8 8H19.8M15.8 16H19.8L12 21L15.8 16M12 8H8.2L12 13L15.8 8H12Z"/></svg>'
                    ],
                    [
                        'name' => 'MySQL 8.0+',
                        'color' => 'from-orange-500 to-orange-600',
                        'iconColor' => 'text-orange-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M16.405 5.501c-.057 0-.112.005-.166.013-.001-.001-.002-.003-.003-.004-1.209.087-2.316.57-3.188 1.333-.01.01-.02.018-.03.027-.01-.009-.02-.017-.03-.027-.872-.763-1.979-1.246-3.188-1.333-.001.001-.002.003-.003.004-.054-.008-.109-.013-.166-.013-2.485 0-4.5 2.015-4.5 4.5s2.015 4.5 4.5 4.5c.057 0 .112-.005.166-.013.001.001.002.003.003.004.87-.062 1.67-.41 2.313-.968.01-.01.02-.018.03-.027.01.009.02.017.03.027.643.558 1.443.906 2.313.968.001-.001.002-.003.003-.004.054.008.109.013.166.013 2.485 0 4.5-2.015 4.5-4.5s-2.015-4.5-4.5-4.5zm-4.405 7.5c-1.381 0-2.5-1.119-2.5-2.5s1.119-2.5 2.5-2.5 2.5 1.119 2.5 2.5-1.119 2.5-2.5 2.5zm4.405 0c-1.381 0-2.5-1.119-2.5-2.5s1.119-2.5 2.5-2.5 2.5 1.119 2.5 2.5-1.119 2.5-2.5 2.5z"/></svg>'
                    ],
                    [
                        'name' => 'Redis',
                        'color' => 'from-red-600 to-red-700',
                        'iconColor' => 'text-red-700',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2V7zm4 0h2v6h-2V7z"/></svg>'
                    ],
                    [
                        'name' => 'Tailwind CSS 4.x',
                        'color' => 'from-cyan-500 to-cyan-600',
                        'iconColor' => 'text-cyan-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6c-2.67 0-4.33 1.33-5 4 1-1.33 2.17-1.83 3.5-1.5.76.19 1.31.74 1.91 1.35.98 1 2.12 2.15 4.59 2.15 2.67 0 4.33-1.33 5-4-1 1.33-2.17 1.83-3.5 1.5-.76-.19-1.31-.74-1.91-1.35C15.61 7.15 14.47 6 12 6zm-5 6c-2.67 0-4.33 1.33-5 4 1-1.33 2.17-1.83 3.5-1.5.76.19 1.31.74 1.91 1.35.98 1 2.12 2.15 4.59 2.15 2.67 0 4.33-1.33 5-4-1 1.33-2.17 1.83-3.5 1.5-.76-.19-1.31-.74-1.91-1.35C10.61 13.15 9.47 12 7 12z"/></svg>'
                    ],
                    [
                        'name' => 'Livewire 4.x',
                        'color' => 'from-purple-500 to-purple-600',
                        'iconColor' => 'text-purple-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>'
                    ],
                    [
                        'name' => 'Alpine.js',
                        'color' => 'from-green-500 to-green-600',
                        'iconColor' => 'text-green-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0L2.5 6v12L12 24l9.5-6V6L12 0zm0 2.18l8 5v9.64l-8 5-8-5V7.18l8-5z"/></svg>'
                    ],
                    [
                        'name' => 'Vite',
                        'color' => 'from-yellow-500 to-yellow-600',
                        'iconColor' => 'text-yellow-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0L2.4 4.8v4.8L12 14.4l9.6-4.8V4.8L12 0zm0 2.4l7.2 3.6-7.2 3.6L4.8 6l7.2-3.6zm0 8.4l7.2 3.6v7.2L12 21.6l-7.2-3.6v-7.2L12 10.8z"/></svg>'
                    ],
                    [
                        'name' => 'Ollama AI',
                        'color' => 'from-indigo-500 to-indigo-600',
                        'iconColor' => 'text-indigo-600',
                        'icon' => '<svg class="w-16 h-16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2V7zm4 0h2v6h-2V7z"/></svg>'
                    ],
                ];
                @endphp
                @foreach($techStack as $tech)
                <div class="group relative bg-white rounded-2xl p-6 md:p-8 border-2 border-gray-200 transition-all duration-300 hover:border-teal-400 hover:shadow-2xl hover:-translate-y-2 transform overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br {{ $tech['color'] }} opacity-0 group-hover:opacity-10 rounded-2xl transition-opacity duration-300"></div>
                    <div class="relative text-center">
                        <div class="flex items-center justify-center mb-4 group-hover:scale-125 group-hover:rotate-6 transition-all duration-300">
                            <div class="{{ $tech['iconColor'] }} drop-shadow-lg filter brightness-110 group-hover:brightness-125 transition-all">
                                {!! $tech['icon'] !!}
                            </div>
                        </div>
                        <div class="font-bold text-gray-800 text-xs md:text-sm leading-tight group-hover:text-teal-600 transition-colors">{{ $tech['name'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- 核心特性 - 更吸引人的卡片设计 -->
        <section class="py-20" id="features">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black gradient-text mb-4 tracking-tight">
                    <span class="inline-block animate-bounce">🌟</span> {{ __('index.section_features') }}
                </h2>
                <p class="text-gray-600 text-lg md:text-xl max-w-2xl mx-auto">
                    专为全球电商运营设计的强大功能
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                $features = [
                    [
                        'icon' => '🌍',
                        'title' => __('index.feature_multi_node_title'),
                        'items' => [
                            __('index.feature_multi_node_1'),
                            __('index.feature_multi_node_2'),
                            __('index.feature_multi_node_3'),
                            __('index.feature_multi_node_4'),
                            __('index.feature_multi_node_5'),
                            __('index.feature_multi_node_6'),
                        ],
                        'gradient' => 'from-blue-500 to-cyan-500'
                    ],
                    [
                        'icon' => '🤖',
                        'title' => __('index.feature_ai_translation_title'),
                        'items' => [
                            __('index.feature_ai_translation_1'),
                            __('index.feature_ai_translation_2'),
                            __('index.feature_ai_translation_3'),
                            __('index.feature_ai_translation_4'),
                            __('index.feature_ai_translation_5'),
                            __('index.feature_ai_translation_6'),
                        ],
                        'gradient' => 'from-purple-500 to-pink-500'
                    ],
                    [
                        'icon' => '🛒',
                        'title' => __('index.feature_chrome_plugin_title'),
                        'items' => [
                            __('index.feature_chrome_plugin_1'),
                            __('index.feature_chrome_plugin_2'),
                            __('index.feature_chrome_plugin_3'),
                            __('index.feature_chrome_plugin_4'),
                            __('index.feature_chrome_plugin_5'),
                            __('index.feature_chrome_plugin_6'),
                        ],
                        'gradient' => 'from-green-500 to-emerald-500'
                    ],
                    [
                        'icon' => '🛍️',
                        'title' => __('index.feature_ecommerce_title'),
                        'items' => [
                            __('index.feature_ecommerce_1'),
                            __('index.feature_ecommerce_2'),
                            __('index.feature_ecommerce_3'),
                            __('index.feature_ecommerce_4'),
                            __('index.feature_ecommerce_5'),
                            __('index.feature_ecommerce_6'),
                        ],
                        'gradient' => 'from-orange-500 to-red-500'
                    ],
                    [
                        'icon' => '🎨',
                        'title' => __('index.feature_admin_title'),
                        'items' => [
                            __('index.feature_admin_1'),
                            __('index.feature_admin_2'),
                            __('index.feature_admin_3'),
                            __('index.feature_admin_4'),
                            __('index.feature_admin_5'),
                            __('index.feature_admin_6'),
                        ],
                        'gradient' => 'from-teal-500 to-cyan-500'
                    ],
                    [
                        'icon' => '⚡',
                        'title' => __('index.feature_performance_title'),
                        'items' => [
                            __('index.feature_performance_1'),
                            __('index.feature_performance_2'),
                            __('index.feature_performance_3'),
                            __('index.feature_performance_4'),
                            __('index.feature_performance_5'),
                            __('index.feature_performance_6'),
                        ],
                        'gradient' => 'from-yellow-500 to-orange-500'
                    ],
                ];
                @endphp

                @foreach($features as $feature)
                <div class="group relative bg-white rounded-3xl p-8 border-2 border-gray-200 transition-all duration-500 shadow-xl hover:shadow-2xl hover:-translate-y-3 hover:border-teal-400 transform overflow-hidden">
                    <!-- 渐变背景 -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br {{ $feature['gradient'] }} opacity-0 group-hover:opacity-10 rounded-full blur-3xl transition-opacity duration-500"></div>
                    
                    <div class="relative z-10">
                        <div class="text-5xl mb-6 group-hover:scale-125 group-hover:rotate-12 transition-all duration-300">{{ $feature['icon'] }}</div>
                        <h3 class="text-2xl font-black text-gray-900 mb-6 leading-tight group-hover:text-teal-600 transition-colors">
                            {{ $feature['title'] }}
                        </h3>
                        <ul class="space-y-3">
                            @foreach($feature['items'] as $item)
                            <li class="flex items-start gap-3 text-gray-700 leading-relaxed">
                                <span class="text-teal-600 font-bold text-lg mt-0.5 flex-shrink-0">✓</span>
                                <span class="text-sm md:text-base">{{ $item }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- 在线演示 - 更突出的设计 -->
        <section class="py-20" id="demo">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black gradient-text mb-4 tracking-tight">
                    <span class="inline-block animate-pulse">🎮</span> {{ __('index.section_demo') }}
                </h2>
                <p class="text-gray-600 text-lg md:text-xl max-w-2xl mx-auto">
                    立即体验，无需注册即可查看完整功能
                </p>
            </div>
            
            <div class="bg-gradient-to-br from-teal-50 to-cyan-50 rounded-3xl p-8 md:p-12 border-2 border-teal-200 shadow-2xl">
                <div class="flex flex-col lg:flex-row gap-8 items-center">
                    <!-- 按钮区域 -->
                    <div class="flex-1 flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="https://demo.chatterup.fun:2003" target="_blank" rel="noopener noreferrer"
                            class="group relative px-8 py-4 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-full font-black text-lg shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 transform overflow-hidden">
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                🚀 {{ __('index.demo_visit_frontend') }}
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-cyan-600 to-teal-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </a>
                        <a href="https://demo.chatterup.fun:2003/manager" target="_blank" rel="noopener noreferrer"
                            class="group px-8 py-4 bg-white text-teal-600 border-2 border-teal-600 rounded-full font-black text-lg shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110 transform hover:bg-teal-50">
                            <span class="flex items-center justify-center gap-2">
                                ⚙️ {{ __('index.demo_visit_admin') }}
                                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </span>
                        </a>
                    </div>
                    
                    <!-- 测试账号信息 -->
                    <div class="flex-1 w-full lg:max-w-md">
                        <div class="bg-white rounded-2xl p-6 shadow-xl border-2 border-teal-200">
                            <h3 class="text-2xl font-black text-teal-600 mb-4">{{ __('index.demo_test_account') }}</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="text-2xl">📧</span>
                                    <div>
                                        <div class="text-xs text-gray-500 font-semibold uppercase">{{ __('index.demo_email') }}</div>
                                        <div class="text-sm font-mono text-gray-900 break-all">demo@demo.com</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="text-2xl">🔑</span>
                                    <div>
                                        <div class="text-xs text-gray-500 font-semibold uppercase">{{ __('index.demo_password') }}</div>
                                        <div class="text-sm font-mono text-gray-900">demo123456</div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200 text-xs text-gray-500 italic">
                                    {{ __('index.demo_account_note') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 警告信息 -->
                <div class="mt-8 p-6 bg-yellow-50 border-2 border-yellow-200 rounded-2xl">
                    <div class="space-y-2 text-sm text-gray-800">
                        <p class="flex items-start gap-2">
                            <span class="text-xl">⚠️</span>
                            <span><strong>{{ __('index.demo_warning_title') }}:</strong> {{ __('index.demo_warning_1') }}</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <span class="text-xl">💻</span>
                            <span>{{ __('index.demo_warning_2') }}</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <span class="text-xl">🌐</span>
                            <span>{{ __('index.demo_warning_3') }}</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <span class="text-xl">📧</span>
                            <span>{{ __('index.demo_warning_4') }} <a href="mailto:hello@teanary.com" class="text-teal-600 font-bold hover:underline">hello@teanary.com</a></span>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 商业服务 - 更吸引人的价格卡片 -->
        <section class="py-20" id="pricing">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black gradient-text mb-4 tracking-tight">
                    <span class="inline-block animate-pulse">💼</span> {{ __('index.section_pricing') }}
                </h2>
                <p class="text-gray-600 text-lg md:text-xl max-w-2xl mx-auto">
                    专业服务，让您的项目快速上线
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                @php
                $pricing = [
                    [
                        'icon' => '🚀',
                        'title' => __('index.pricing_deployment_title'),
                        'price' => '¥500',
                        'priceUnit' => __('index.pricing_deployment_price'),
                        'items' => [
                            __('index.pricing_deployment_1'),
                            __('index.pricing_deployment_2'),
                            __('index.pricing_deployment_3'),
                            __('index.pricing_deployment_4'),
                            __('index.pricing_deployment_5'),
                            __('index.pricing_deployment_6'),
                        ],
                        'email' => '部署服务咨询',
                        'gradient' => 'from-blue-500 to-cyan-500'
                    ],
                    [
                        'icon' => '🔧',
                        'title' => __('index.pricing_maintenance_title'),
                        'price' => '¥1500',
                        'priceUnit' => __('index.pricing_maintenance_price'),
                        'items' => [
                            __('index.pricing_maintenance_1'),
                            __('index.pricing_maintenance_2'),
                            __('index.pricing_maintenance_3'),
                            __('index.pricing_maintenance_4'),
                            __('index.pricing_maintenance_5'),
                            __('index.pricing_maintenance_6'),
                        ],
                        'email' => '维护服务咨询',
                        'gradient' => 'from-green-500 to-emerald-500'
                    ],
                    [
                        'icon' => '🛒',
                        'title' => __('index.pricing_plugin_title'),
                        'price' => '¥1500',
                        'priceUnit' => '',
                        'items' => [
                            __('index.pricing_plugin_1'),
                            __('index.pricing_plugin_2'),
                            __('index.pricing_plugin_3'),
                            __('index.pricing_plugin_4'),
                            __('index.pricing_plugin_5'),
                            __('index.pricing_plugin_6'),
                            __('index.pricing_plugin_7'),
                        ],
                        'email' => '采集插件咨询',
                        'gradient' => 'from-purple-500 to-pink-500'
                    ],
                    [
                        'icon' => '🤖',
                        'title' => __('index.pricing_translation_title'),
                        'price' => '¥1500',
                        'priceUnit' => '',
                        'items' => [
                            __('index.pricing_translation_1'),
                            __('index.pricing_translation_2'),
                            __('index.pricing_translation_3'),
                            __('index.pricing_translation_4'),
                            __('index.pricing_translation_5'),
                            __('index.pricing_translation_6'),
                            __('index.pricing_translation_7'),
                        ],
                        'email' => '翻译端程序咨询',
                        'gradient' => 'from-orange-500 to-red-500'
                    ],
                    [
                        'icon' => '🎨',
                        'title' => __('index.pricing_custom_title'),
                        'price' => __('index.pricing_custom_price'),
                        'priceUnit' => '',
                        'items' => [
                            __('index.pricing_custom_1'),
                            __('index.pricing_custom_2'),
                            __('index.pricing_custom_3'),
                            __('index.pricing_custom_4'),
                            __('index.pricing_custom_5'),
                            __('index.pricing_custom_6'),
                        ],
                        'email' => '二次开发咨询',
                        'gradient' => 'from-teal-500 to-cyan-500'
                    ],
                ];
                @endphp

                @foreach($pricing as $index => $service)
                <div class="group relative bg-white rounded-3xl p-8 border-2 border-gray-200 transition-all duration-500 shadow-xl hover:shadow-2xl hover:-translate-y-3 hover:border-teal-400 transform overflow-hidden">
                    <!-- 渐变顶部条 -->
                    <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r {{ $service['gradient'] }}"></div>
                    
                    <!-- 悬浮时的背景效果 -->
                    <div class="absolute inset-0 bg-gradient-to-br {{ $service['gradient'] }} opacity-0 group-hover:opacity-5 transition-opacity duration-500"></div>
                    
                    <div class="relative z-10 text-center">
                        <div class="text-5xl mb-4 group-hover:scale-125 group-hover:rotate-12 transition-all duration-300">{{ $service['icon'] }}</div>
                        <h3 class="text-2xl font-black text-gray-900 mb-6 group-hover:text-teal-600 transition-colors">{{ $service['title'] }}</h3>
                        <div class="mb-6">
                            <div class="text-5xl font-black gradient-text leading-none mb-2">{{ $service['price'] }}</div>
                            @if($service['priceUnit'])
                            <div class="text-sm text-gray-600 font-semibold">{{ $service['priceUnit'] }}</div>
                            @endif
                        </div>
                        <ul class="text-left space-y-3 mb-8 min-h-[200px]">
                            @foreach($service['items'] as $item)
                            <li class="flex items-start gap-2 text-gray-700 text-sm">
                                <span class="text-teal-600 font-bold text-lg mt-0.5 flex-shrink-0">✓</span>
                                <span>{{ $item }}</span>
                            </li>
                            @endforeach
                        </ul>
                        <a href="mailto:hello@teanary.com?subject={{ $service['email'] }}"
                            class="block w-full px-6 py-3 bg-gradient-to-r {{ $service['gradient'] }} text-white rounded-full font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 transform">
                            {{ __('index.pricing_deployment_consult') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- 开源协议 -->
        <section class="py-20" id="license">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black gradient-text mb-4 tracking-tight">
                    <span class="inline-block animate-bounce">📄</span> {{ __('index.license_title') }}
                </h2>
                <p class="text-gray-600 text-lg md:text-xl max-w-2xl mx-auto">
                    {{ __('index.license_desc', ['license' => 'AGPL-3.0']) }}
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                $licenseSections = [
                    [
                        'icon' => '✅',
                        'title' => __('index.license_can_title'),
                        'items' => [
                            __('index.license_can_1'),
                            __('index.license_can_2'),
                            __('index.license_can_3'),
                        ],
                        'color' => 'from-green-500 to-emerald-500'
                    ],
                    [
                        'icon' => '⚠️',
                        'title' => __('index.license_must_title'),
                        'items' => [
                            __('index.license_must_1'),
                            __('index.license_must_2'),
                            __('index.license_must_3'),
                        ],
                        'color' => 'from-yellow-500 to-orange-500'
                    ],
                    [
                        'icon' => '❌',
                        'title' => __('index.license_cannot_title'),
                        'items' => [
                            __('index.license_cannot_1'),
                            __('index.license_cannot_2'),
                            __('index.license_cannot_3'),
                        ],
                        'color' => 'from-red-500 to-pink-500'
                    ],
                ];
                @endphp

                @foreach($licenseSections as $section)
                <div class="bg-white rounded-2xl p-8 border-2 border-gray-200 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="text-4xl mb-4">{{ $section['icon'] }}</div>
                    <h3 class="text-2xl font-black text-gray-900 mb-6">{{ $section['title'] }}</h3>
                    <ul class="space-y-3">
                        @foreach($section['items'] as $item)
                        <li class="flex items-start gap-3 text-gray-700">
                            <span class="text-teal-600 font-bold text-lg mt-0.5 flex-shrink-0">•</span>
                            <span class="text-sm md:text-base">{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </section>
    </div>
</div>

<x-seo-meta title="{{ __('index.seo_title') }}" description="{{ __('index.seo_description') }}" />
