@props([
    'value' => '',
    'label' => null,
    'showFull' => false,
    'maxLength' => 20,
    'class' => '',
    'compact' => false,
])

@php
    $displayValue = $showFull ? $value : \Illuminate\Support\Str::limit($value, $maxLength);
    $inputId = 'copy-input-' . uniqid();
@endphp

<div class="copy-to-clipboard-wrapper {{ $class }}" x-data="{ copied: false }">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
    @endif
    
    <div class="flex items-center gap-2 {{ $compact ? 'inline-flex' : '' }}">
        @if($showFull)
            <input 
                type="text" 
                id="{{ $inputId }}"
                value="{{ $value }}"
                readonly
                class="flex-1 rounded-lg border-gray-300 shadow-sm bg-gray-50 px-4 py-2 text-sm"
            />
        @else
            <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono {{ $compact ? '' : 'flex-1' }}">{{ $displayValue }}</code>
        @endif
        
        <button
            type="button"
            @click="
                navigator.clipboard.writeText('{{ $value }}').then(() => {
                    copied = true;
                    setTimeout(() => copied = false, 2000);
                });
            "
            class="inline-flex items-center justify-center {{ $compact ? 'p-1.5' : 'px-3 py-2' }} text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors"
            title="复制到剪贴板"
        >
            <svg x-show="!copied" class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <svg x-show="copied" class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }} text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </button>
    </div>
</div>
