@props([
    'name' => null,
    'id' => null,
    'wire' => null,
    'accept' => 'image/*',
    'multiple' => false,
    'preview' => null, // 预览图片 URL
    'previewSize' => 'w-32 h-32', // 预览图片尺寸
    'label' => null,
    'help' => null,
    'error' => null,
    'showPreview' => true,
    'variant' => 'default', // default, button, hidden
    'class' => '',
])

@php
    $inputId = $id ?? $name ?? 'file-upload-' . uniqid();
    $baseClasses = 'w-full rounded-lg border-2 border-teal-100 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors';
    
    // 根据 variant 设置不同的样式
    if ($variant === 'button') {
        $baseClasses = 'hidden';
    } elseif ($variant === 'hidden') {
        $baseClasses = 'hidden';
    }
    
    $classes = trim($baseClasses . ' ' . $class);
    
    // Parse wire attribute
    $wireDirective = null;
    if ($wire) {
        if (str_contains($wire, '=')) {
            [$modifiers, $model] = explode('=', $wire, 2);
            $wireDirective = 'wire:model.' . str_replace('.', '.', $modifiers) . '="' . $model . '"';
        } else {
            $wireDirective = 'wire:model="' . $wire . '"';
        }
    }
@endphp

<div class="space-y-3">
    <div class="flex flex-col sm:flex-row gap-4 items-start">
        @if($showPreview && $preview)
            <div class="flex-shrink-0">
                <div class="{{ $previewSize }} overflow-hidden border-2 border-teal-100 shadow-md bg-gradient-to-br from-gray-50 to-gray-100 {{ str_contains($previewSize, 'rounded') ? '' : 'rounded-xl' }}">
                    <img src="{{ $preview }}" alt="Preview" class="w-full h-full object-cover">
                </div>
            </div>
        @elseif($showPreview && !$preview && $variant === 'button')
            <div class="flex-shrink-0">
                <div class="{{ $previewSize }} rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border-2 border-teal-100 shadow-sm">
                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        @endif

        <div class="flex-1 w-full">
            @if($label && $variant !== 'button')
                <x-widgets.label :for="$inputId" class="text-base font-semibold text-gray-800">
                    {{ $label }}
                </x-widgets.label>
            @endif
            
            @if($variant === 'button')
                <label for="{{ $inputId }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-gray-700 bg-white border-2 border-teal-100 rounded-xl hover:bg-gray-50 hover:border-teal-300 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>{{ $label ?? (__('app.upload') ?? '上传文件') }}</span>
                </label>
            @endif

            <input 
                type="file"
                @if($inputId) id="{{ $inputId }}" @endif
                @if($name) name="{{ $name }}" @endif
                @if($accept) accept="{{ $accept }}" @endif
                @if($multiple) multiple @endif
                @if($wireDirective) {!! $wireDirective !!} @endif
                class="{{ $classes }} file:mr-4 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 file:transition-colors file:cursor-pointer"
                {{ $attributes->except(['name', 'id', 'wire', 'accept', 'multiple', 'preview', 'previewSize', 'label', 'help', 'error', 'showPreview', 'variant', 'class']) }}
            />

            @if($error)
                @error($error)
                    <p class="mt-2 text-sm font-medium text-red-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            @endif

            @if($help)
                <p class="mt-2 text-xs text-gray-500 leading-relaxed">{{ $help }}</p>
            @endif
        </div>
    </div>
</div>
