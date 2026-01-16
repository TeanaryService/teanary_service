<div class="min-h-screen bg-gray-50 font-chinese antialiased pt-0">
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

        <!-- Floating Action Buttons -->
        <div class="absolute top-5 right-5 flex gap-2 z-10">
            <a href="https://demo.chatterup.fun:2003" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-1.5 px-4 py-2 bg-white/95 backdrop-blur-sm text-teal-600 no-underline rounded-full text-sm font-semibold border border-white/30 transition-all duration-300 shadow-lg hover:bg-white hover:text-teal-700 hover:scale-110 hover:shadow-xl hover:rotate-1"
                title="在线演示">
                <span class="text-base">🎮</span>
                <span class="hidden sm:inline">在线演示</span>
            </a>
            <a href="https://github.com/TeanaryService/teanary_srvice" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-1.5 px-4 py-2 bg-white/95 backdrop-blur-sm text-teal-600 no-underline rounded-full text-sm font-semibold border border-white/30 transition-all duration-300 shadow-lg hover:bg-white hover:text-teal-700 hover:scale-110 hover:shadow-xl hover:-rotate-1"
                title="GitHub">
                <span class="text-base">⭐</span>
                <span class="hidden sm:inline">GitHub</span>
            </a>
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
                🌍 全球多节点电商平台系统 - 支持多节点部署、AI自动翻译、商品采集的现代化电商解决方案 🚀
            </p>
            <div class="flex justify-center gap-3 flex-wrap mt-8 relative z-10">
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">✨ AGPL-3.0 开源</span>
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">⚡ Laravel 12</span>
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">🌐 多节点同步</span>
                <span
                    class="bg-gradient-to-r from-white/95 to-white/90 backdrop-blur-sm text-teal-600 px-4 py-2 rounded-full text-sm font-semibold border-2 border-white/40 transition-all duration-300 shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">🤖 AI 翻译</span>
            </div>
        </div>
    </header>

    <!-- Sticky Navigation Bar -->
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-6 md:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center gap-1 text-sm text-gray-600">
                    <span class="animate-bounce">📍</span>
                    <span>快速导航</span>
                </div>
                <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                    <a href="#tech-stack" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🚀 技术栈</a>
                    <a href="#features" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🌟 核心特性</a>
                    <a href="#demo" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">🎮 在线演示</a>
                    <a href="#version" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">📦 版本</a>
                    <a href="#pricing" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">💼 服务</a>
                    <a href="#license" class="px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 transition-all duration-300 hover:scale-105 transform">📄 协议</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 md:px-8 relative z-20">
        <!-- 技术栈 -->
        <div class="my-16" id="tech-stack">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center tracking-tight relative pb-4 mb-16">
                <span class="inline-block animate-spin-slow">🚀</span> 技术栈
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-purple-600 to-transparent rounded-full"></div>
            </h2>
            <div class="flex flex-wrap gap-3 justify-center mt-4">
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Laravel 12.x</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">PHP 8.1+</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">MySQL 8.0+</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">Redis</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Tailwind CSS 3.x</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">Livewire 3.x</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Filament 3.x</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:-rotate-1 transform">Laravel Octane</div>
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 text-white px-6 py-3 rounded-full font-semibold border-none transition-all duration-300 text-sm shadow-lg hover:scale-110 hover:shadow-xl hover:rotate-1 transform">Ollama AI</div>
            </div>
        </div>

        <!-- 核心特性 -->
        <div class="my-16" id="features">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-16 text-center tracking-tight relative pb-4">
                <span class="inline-block animate-bounce">🌟</span> 核心特性
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-teal-600 to-transparent rounded-full"></div>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mt-4">
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-bounce">🌍</span> 多节点数据同步
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            解决跨国服务器管理难题</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            中国管理节点，全球销售节点</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            双向数据自动同步</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            支持任意数量节点</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            自动重试和故障恢复</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            完整的同步监控</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-pulse">🤖</span> AI 自动翻译
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            支持 8 种语言自动翻译</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            商品信息自动翻译</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            文章内容智能翻译</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            集成 Ollama 本地 AI</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            批量翻译处理</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            翻译状态实时跟踪</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-bounce">🛒</span> Chrome 插件采集
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            1688 商品一键采集</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            图片自动下载上传</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            多语言数据处理</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            批量商品导入</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            自动同步到所有节点</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            简化商品管理流程</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-pulse">🛍️</span> 完整电商功能
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            多规格商品管理</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            多语言产品信息</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            购物车和订单系统</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            支付集成（PayPal等）</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            促销和优惠券系统</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            用户管理系统</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-bounce">🎨</span> 现代化管理后台
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            Filament 3.x 管理面板</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            实时数据统计</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            多语言内容管理</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            媒体文件管理</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            系统配置管理</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            响应式设计</li>
                    </ul>
                </div>
                <div
                    class="bg-white border-2 border-gray-200 rounded-2xl p-6 transition-all duration-300 shadow-md relative h-full flex flex-col group hover:shadow-2xl hover:-translate-y-2 hover:border-teal-400 hover:scale-105 transform">
                    <h3 class="text-teal-600 mb-4 text-xl font-semibold leading-tight">
                        <span class="inline-block animate-pulse">⚡</span> 高性能架构
                    </h3>
                    <ul class="list-none p-0 m-0 flex-1">
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            Laravel Octane 高性能服务器</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            Redis 缓存加速</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            队列异步处理</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            CDN 静态资源加速</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            数据库优化</li>
                        <li
                            class="py-2 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            批量操作优化</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 在线演示 -->
        <div class="my-16" id="demo">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center tracking-tight relative pb-4 mb-16">
                <span class="inline-block animate-pulse">🎮</span> 在线演示
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-blue-600 to-transparent rounded-full"></div>
            </h2>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center shadow-sm flex items-center justify-center">
                <div class="flex gap-3 justify-center flex-wrap mb-5">
                    <a href="https://demo.chatterup.fun:2003" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold text-base transition-all duration-300 shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg border-none">
                        🚀 访问前端演示
                    </a>
                    <a href="https://demo.chatterup.fun:2003/m" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-white text-teal-600 no-underline rounded-md font-semibold text-base transition-all duration-300 shadow-sm hover:bg-gray-50 hover:border-teal-700 border border-teal-600">
                        ⚙️ 访问后台管理
                    </a>
                </div>
                <div
                    class="p-5 text-left inline-block min-w-[280px] shadow-sm">
                    <h3 class="text-teal-600 mb-3 text-lg font-semibold">测试账号信息</h3>
                    <div class="text-gray-600 leading-relaxed text-sm">
                        <div class="mb-2"><strong class="text-gray-900 font-semibold">邮箱:</strong> demo@demo.com</div>
                        <div class="mb-2"><strong class="text-gray-900 font-semibold">密码:</strong> demo123456</div>
                        <div class="mt-4 pt-4 border-t border-gray-200 text-gray-500 italic text-xs">前后端使用相同账号密码</div>
                    </div>
                </div>
                <div class="mt-5 text-gray-900 text-sm leading-relaxed px-6 md:px-10">
                    <p class="mb-1.5">⚠️ <strong>重要提示:</strong> Demo 数据每 8 小时自动重置一次</p>
                    <p class="mb-1.5">💻 当前 Demo 服务器运行在一台树莓派上，性能有限，请谅解</p>
                    <p class="mb-1.5">🌐 如果您愿意赞助服务器资源部署 Demo，我们可以部署多节点同步演示环境，展示完整的多节点同步功能</p>
                    <p class="mb-1.5">📧 如有赞助意向或想了解更多信息，请联系：<a href="mailto:hello@teanary.com"
                            class="text-teal-600 no-underline font-medium transition-colors hover:text-teal-700 hover:underline">hello@teanary.com</a>
                    </p>
                </div>
            </div>
            <!-- 技术选择说明 -->
            <div>
                <h3 class="text-gray-900 my-16 text-xl font-semibold text-center">💡 为什么选择程序层同步而不是 MySQL 主从同步？</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 my-5">
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🌍 高延迟环境可靠性</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">跨国网络延迟通常在 200-400ms，MySQL
                            主从同步在高延迟环境下容易出现超时和连接中断。程序层同步通过 HTTP/HTTPS API 可以更好地处理网络波动，支持重试机制。</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🔄 灵活同步策略</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">
                            支持批量同步、选择性同步、双向同步等灵活策略。可以只同步需要的数据，避免同步不必要的系统表，而 MySQL 主从通常是全量单向同步。</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🛡️ 更好的容错能力</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">
                            同步失败可以自动重试，不会因为网络波动导致数据丢失。完整的同步日志和状态跟踪，可以清楚地知道每条数据的同步状态。</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🔐 安全性优势</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">使用 API Key 进行认证，比直接暴露数据库连接更安全。所有数据传输通过 HTTPS
                            加密，保护数据安全。</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">📁 文件同步支持</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">可以同时同步媒体文件、图片等，而 MySQL
                            主从同步无法处理文件。在同步过程中可以进行数据转换、验证和业务逻辑处理。</p>
                    </div>
                    <div
                        class="bg-white rounded-lg p-5 border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                        <h4 class="text-teal-600 mb-2.5 text-base font-semibold">🗄️ 跨数据库兼容</h4>
                        <p class="text-gray-900 leading-relaxed text-sm">不依赖特定的数据库类型，可以支持 MySQL、PostgreSQL
                            等不同数据库。未来如果需要支持其他数据库类型，只需修改同步逻辑。</p>
                    </div>
                </div>
                <div class="mt-6 p-4.5 bg-white rounded-lg text-center shadow-sm border border-gray-200">
                    <p class="text-gray-600 leading-relaxed text-sm"><strong
                            class="text-teal-600 font-semibold">总结：</strong>在跨国高延迟网络环境下，程序层同步提供了更好的可靠性、灵活性和可维护性，更适合复杂的多节点电商场景。
                    </p>
                </div>
            </div>
        </div>

        <!-- 发行版本 -->
        <div class="my-16" id="version">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center tracking-tight relative pb-4 mb-16">
                <span class="inline-block animate-bounce">📦</span> 发行版本
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-orange-600 to-transparent rounded-full"></div>
            </h2>
            <div class="my-6">
                <div class="flex justify-between items-center mb-5 flex-wrap gap-4">
                    <div class="flex items-baseline gap-4">
                        <div class="text-3xl font-bold text-teal-600">v1.0.0</div>
                        <div class="text-gray-600 text-sm">2026-01-11</div>
                    </div>
                    <span
                        class="bg-teal-600 text-white px-4 py-1.5 rounded-full text-sm font-semibold shadow-md">最新版本</span>
                </div>
                <div class="text-gray-600 leading-relaxed mb-6 text-sm">
                    <strong class="text-gray-900">🎉 首个正式版本发布！</strong><br>
                    这是一个功能完整的全球多节点电商平台系统，包含多节点数据同步、AI自动翻译、商品采集等核心功能。
                    系统采用现代化的技术栈，提供高性能、可扩展的电商解决方案。
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-5">
                    <div
                        class="bg-white rounded-lg p-3.5 border-l-4 border-teal-600 flex items-start gap-2.5 shadow-sm transition-all duration-300 hover:shadow-lg hover:translate-x-0.5 hover:border-teal-700">
                        <strong class="text-teal-600 min-w-[60px] text-sm font-semibold">✨ 新增：</strong>
                        <span class="flex-1 text-gray-600 text-sm">多节点数据双向同步系统</span>
                    </div>
                    <div
                        class="bg-white rounded-lg p-3.5 border-l-4 border-teal-600 flex items-start gap-2.5 shadow-sm transition-all duration-300 hover:shadow-lg hover:translate-x-0.5 hover:border-teal-700">
                        <strong class="text-teal-600 min-w-[60px] text-sm font-semibold">✨ 新增：</strong>
                        <span class="flex-1 text-gray-600 text-sm">AI自动翻译功能（支持8种语言）</span>
                    </div>
                    <div
                        class="bg-white rounded-lg p-3.5 border-l-4 border-teal-600 flex items-start gap-2.5 shadow-sm transition-all duration-300 hover:shadow-lg hover:translate-x-0.5 hover:border-teal-700">
                        <strong class="text-teal-600 min-w-[60px] text-sm font-semibold">✨ 新增：</strong>
                        <span class="flex-1 text-gray-600 text-sm">Chrome插件商品采集工具</span>
                    </div>
                    <div
                        class="bg-white rounded-lg p-3.5 border-l-4 border-teal-600 flex items-start gap-2.5 shadow-sm transition-all duration-300 hover:shadow-lg hover:translate-x-0.5 hover:border-teal-700">
                        <strong class="text-teal-600 min-w-[60px] text-sm font-semibold">✨ 新增：</strong>
                        <span class="flex-1 text-gray-600 text-sm">完整的电商功能模块</span>
                    </div>
                    <div
                        class="bg-white rounded-lg p-3.5 border-l-4 border-teal-600 flex items-start gap-2.5 shadow-sm transition-all duration-300 hover:shadow-lg hover:translate-x-0.5 hover:border-teal-700">
                        <strong class="text-teal-600 min-w-[60px] text-sm font-semibold">✨ 新增：</strong>
                        <span class="flex-1 text-gray-600 text-sm">现代化Filament管理后台</span>
                    </div>
                    <div
                        class="bg-white rounded-lg p-3.5 border-l-4 border-teal-600 flex items-start gap-2.5 shadow-sm transition-all duration-300 hover:shadow-lg hover:translate-x-0.5 hover:border-teal-700">
                        <strong class="text-teal-600 min-w-[60px] text-sm font-semibold">✨ 新增：</strong>
                        <span class="flex-1 text-gray-600 text-sm">Laravel Octane高性能部署</span>
                    </div>
                </div>
                <div class="flex gap-3 flex-wrap mt-6">
                    <a href="https://github.com/TeanaryService/teanary_srvice/releases" target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white text-teal-600 no-underline rounded-md font-medium transition-all duration-300 text-sm border border-teal-600 shadow-sm hover:bg-teal-600 hover:text-white hover:border-teal-700 hover:-translate-y-0.5 hover:shadow-lg">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                        </svg>
                        查看所有版本
                    </a>
                    <a href="https://github.com/TeanaryService/teanary_srvice/archive/refs/tags/v1.0.0.zip"
                        target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white text-teal-600 no-underline rounded-md font-medium transition-all duration-300 text-sm border border-teal-600 shadow-sm hover:bg-teal-600 hover:text-white hover:border-teal-700 hover:-translate-y-0.5 hover:shadow-lg">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" />
                        </svg>
                        下载 ZIP
                    </a>
                    <a href="https://github.com/TeanaryService/teanary_srvice/archive/refs/tags/v1.0.0.tar.gz"
                        target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white text-teal-600 no-underline rounded-md font-medium transition-all duration-300 text-sm border border-teal-600 shadow-sm hover:bg-teal-600 hover:text-white hover:border-teal-700 hover:-translate-y-0.5 hover:shadow-lg">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" />
                        </svg>
                        下载 TAR.GZ
                    </a>
                </div>
            </div>
        </div>

        <!-- 商业服务 -->
        <div class="my-16" id="pricing">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 my-8 text-center tracking-tight relative">
                <span class="inline-block animate-pulse">💼</span> 商业服务
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-transparent via-pink-600 to-transparent rounded-full"></div>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5 mt-4">
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🚀 部署服务</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">
                        ¥500<small class="ml-2.5 text-sm text-gray-600 font-medium">/次</small>
                    </div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            服务器环境配置</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            代码部署和优化</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            数据库配置</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            多节点同步配置</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            SSL 证书配置</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            性能优化</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=部署服务咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">立即咨询</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🔧 维护服务</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">
                        ¥1500<small class="ml-2.5 text-sm text-gray-600 font-medium">/年</small>
                    </div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            系统更新和维护</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            安全补丁更新</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            性能监控和优化</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            技术支持（邮件/电话）</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            故障排查和修复</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            数据备份和恢复</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=维护服务咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">立即咨询</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🛒 采集插件</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">¥1500</div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            Chrome 浏览器插件</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            1688 商品一键采集</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            图片自动下载上传</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            批量商品导入</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            3年免费更新</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            3年技术支持</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            使用教程和文档</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=采集插件咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">立即咨询</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🤖 翻译端程序</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">¥1500</div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            独立的翻译服务程序</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            集成 Ollama AI 模型</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            支持 8 种语言翻译</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            商品和文章批量翻译</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            3年免费更新</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            3年技术支持</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            部署指导和技术文档</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=翻译端程序咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">立即咨询</a>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg p-7 text-center transition-all duration-300 relative shadow-sm flex flex-col group hover:shadow-lg hover:-translate-y-1 hover:border-teal-400">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-600 via-teal-400 to-teal-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <h3 class="text-2xl font-bold mb-5 text-teal-600">🎨 界面二次开发</h3>
                    <div class="text-4xl font-bold my-4 text-teal-600 leading-none tracking-tight">
                        定制<small class="ml-2.5 text-sm text-gray-600 font-medium">报价</small>
                    </div>
                    <ul class="list-none text-left my-6 p-0 flex-1">
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            自定义主题开发</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            界面定制和优化</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            新功能开发</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            第三方系统集成</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            专业设计支持</li>
                        <li
                            class="py-2.5 pl-6 relative text-gray-600 leading-relaxed text-sm before:content-['✓'] before:absolute before:left-0 before:text-teal-600 before:font-semibold before:text-base">
                            长期技术支持</li>
                    </ul>
                    <a href="mailto:hello@teanary.com?subject=二次开发咨询"
                        class="inline-block px-8 py-3 bg-teal-600 text-white no-underline rounded-md font-semibold mt-5 transition-all duration-300 relative overflow-hidden border-none cursor-pointer text-sm shadow-md hover:bg-teal-700 hover:-translate-y-0.5 hover:shadow-lg">立即咨询</a>
                </div>
            </div>
        </div>

        <!-- 开源协议 -->
        <div class="my-16" id="license"">
            <h3 class="text-gray-900 mb-4 text-2xl font-semibold">
                <span class="inline-block animate-bounce">📄</span> 开源协议
            </h3>
            <p class="text-gray-600 mb-6 text-sm leading-relaxed">本项目采用 <strong
                    class="text-gray-900 font-semibold">AGPL-3.0</strong> (GNU Affero General Public License v3.0)
                开源协议。</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
                <div
                    class="p-5 bg-white rounded-lg border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                    <h4 class="text-teal-600 mb-3 text-lg font-semibold">✅ 您可以</h4>
                    <ul class="list-none p-0">
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            自由使用、研究、修改</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            自由分发代码</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            用于商业项目</li>
                    </ul>
                </div>
                <div
                    class="p-5 bg-white rounded-lg border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                    <h4 class="text-teal-600 mb-3 text-lg font-semibold">⚠️ 您必须</h4>
                    <ul class="list-none p-0">
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            公开修改后的源代码</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            保留版权声明</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            使用相同协议</li>
                    </ul>
                </div>
                <div
                    class="p-5 bg-white rounded-lg border-l-4 border-teal-600 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:border-teal-700">
                    <h4 class="text-teal-600 mb-3 text-lg font-semibold">❌ 您不能</h4>
                    <ul class="list-none p-0">
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            修改后闭源售卖</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            移除版权声明</li>
                        <li
                            class="py-1.5 text-gray-600 pl-5 relative leading-relaxed text-sm before:content-['•'] before:absolute before:left-0 before:text-teal-600 before:font-bold before:text-lg">
                            违反协议规定</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="全球多节点电商平台系统" description="支持多节点部署、AI自动翻译、商品采集的现代化全球电商平台系统"/>
@endPushOnce
