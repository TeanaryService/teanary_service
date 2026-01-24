{{-- Flash 消息提示区域（右上角） --}}
<div class="fixed top-24 right-4 md:right-8 z-50 space-y-3 max-w-md w-full md:w-auto pointer-events-none" 
     x-data="{ 
         messages: [],
         addMessage(type, message) {
             const id = 'msg-' + Date.now() + '-' + Math.random();
             this.messages.push({ id, type, message });
             setTimeout(() => this.removeMessage(id), 5000);
         },
         removeMessage(id) {
             this.messages = this.messages.filter(m => m.id !== id);
         }
     }"
     x-init="
         // 监听 Livewire 事件
         document.addEventListener('livewire:init', () => {
             Livewire.on('flash-message', (event) => {
                 // Livewire 3 事件格式处理
                 const data = event && typeof event === 'object' ? event : { type: 'info', message: event };
                 addMessage(data.type || 'info', data.message || event);
             });
         });
         
         // 如果 Livewire 已经初始化，直接监听
         if (window.Livewire) {
             Livewire.on('flash-message', (event) => {
                 const data = event && typeof event === 'object' ? event : { type: 'info', message: event };
                 addMessage(data.type || 'info', data.message || event);
             });
         }
         
         // 监听 session flash 消息（用于非 Livewire 页面）
         @if(session('success'))
             addMessage('success', @js(session('success')));
         @endif
         @if(session('error'))
             addMessage('error', @js(session('error')));
         @endif
         @if(session('message'))
             addMessage('info', @js(session('message')));
         @endif
         @if(session('warning'))
             addMessage('warning', @js(session('warning')));
         @endif
     ">
    <template x-for="(msg, index) in messages" :key="msg.id">
        <div x-show="true"
             :style="`z-index: ${9999 - index}`"
             class="pointer-events-auto"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform ease-in duration-200 transition"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-full">
            <div 
                x-data="{ show: true }" 
                x-show="show"
                x-bind:class="{
                    'bg-teal-50 border-teal-200 text-teal-900': msg.type === 'success',
                    'bg-red-50 border-red-200 text-red-900': msg.type === 'error',
                    'bg-yellow-50 border-yellow-200 text-yellow-900': msg.type === 'warning',
                    'bg-blue-50 border-blue-200 text-blue-900': msg.type === 'info'
                }"
                class="rounded-xl border-2 p-4 shadow-lg"
                role="alert">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg x-show="msg.type === 'success'" class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg x-show="msg.type === 'error'" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg x-show="msg.type === 'warning'" class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <svg x-show="msg.type === 'info'" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold" x-text="msg.message"></p>
                    </div>
                    <button 
                        type="button"
                        @click="show = false; removeMessage(msg.id)"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
