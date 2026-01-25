@props([
    // Livewire binding
    'wire' => null,           // e.g. "avatar" or "live=avatar"
    'upload' => null,         // TemporaryUploadedFile|array<TemporaryUploadedFile>|null (用于临时预览)

    // Existing media preview
    'preview' => null,        // string|null (已有图片 URL)
    'existing' => [],         // Media[]|Collection (多图已存在媒体)
    'conversion' => 'thumb',  // 多图/已有媒体优先使用的 conversion

    // Input options
    'accept' => 'image/*',
    'multiple' => false,

    // UI
    'label' => null,
    'help' => null,
    'error' => null,
    'variant' => 'default',   // default, button, hidden
    'previewSize' => 'w-32 h-32',
    'itemSize' => 'w-20 h-20', // 多图缩略图尺寸
    'showTempPreview' => true,
    'uploadingText' => '正在上传图片…',
])

@php
    $existingItems = $existing instanceof \Illuminate\Support\Collection ? $existing : collect($existing);

    // Parse wire target (for wire:loading)
    $wireTarget = null;
    if ($wire) {
        if (str_contains($wire, '=')) {
            [, $model] = explode('=', $wire, 2);
            $wireTarget = $model;
        } else {
            $wireTarget = $wire;
        }
    }

    // 临时预览（单图/多图）
    $tempUrls = [];
    if ($showTempPreview && $upload) {
        $uploads = is_array($upload) || $upload instanceof \Illuminate\Support\Collection ? $upload : [$upload];
        foreach ($uploads as $u) {
            if ($u && method_exists($u, 'temporaryUrl')) {
                try {
                    $tempUrls[] = $u->temporaryUrl();
                } catch (\Throwable) {
                    // ignore
                }
            }
        }
    }

    // 已有媒体 URL（thumb 未生成则回退原图）
    $existingMediaUrls = [];
    if ($existingItems->isNotEmpty()) {
        foreach ($existingItems as $media) {
            if (! $media) {
                continue;
            }
            $existingMediaUrls[] =
                method_exists($media, 'hasGeneratedConversion') && $media->hasGeneratedConversion($conversion)
                    ? $media->getUrl($conversion)
                    : $media->getUrl();
        }
    }

    // 单图展示优先级：临时预览 > preview(url) > existing(media 首张)
    $singlePreview = $tempUrls[0] ?? $preview ?? ($existingMediaUrls[0] ?? null);
@endphp

<div class="space-y-3">
    @if($multiple)
        {{-- 多图：已有媒体 + 临时预览（不会依赖 thumb 一定存在） --}}
        @if(!empty($existingMediaUrls))
            <div class="flex flex-wrap gap-3">
                @foreach($existingMediaUrls as $src)
                    <div class="{{ $itemSize }} rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                        <img src="{{ $src }}" alt="" class="w-full h-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($tempUrls))
            <div class="space-y-2">
                <div class="text-xs text-gray-600">
                    已选择 {{ count($tempUrls) }} 张图片
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach($tempUrls as $src)
                        <div class="{{ $itemSize }} rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                            <img src="{{ $src }}" alt="" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        {{-- 单图：头像/封面等 --}}
        @if($singlePreview)
            <div class="flex-shrink-0">
                <div class="{{ $previewSize }} overflow-hidden border-2 border-teal-100 shadow-md bg-gradient-to-br from-gray-50 to-gray-100 {{ str_contains($previewSize, 'rounded') ? '' : 'rounded-xl' }}">
                    <img src="{{ $singlePreview }}" alt="Preview" class="w-full h-full object-cover">
                </div>
            </div>
        @elseif($variant === 'button')
            <div class="flex-shrink-0">
                <div class="{{ $previewSize }} rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border-2 border-teal-100 shadow-sm">
                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        @endif
    @endif

    {{-- 统一的 input（样式沿用旧组件），支持 variant=button/hidden --}}
    @php
        $inputId = $attributes->get('id') ?? 'image-upload-' . uniqid();
        $baseClasses = 'w-full rounded-lg border-2 border-teal-100 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors';
        if ($variant === 'button' || $variant === 'hidden') {
            $baseClasses = 'hidden';
        }
        $classes = trim($baseClasses . ' ' . ($attributes->get('class') ?? ''));
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
            <span>{{ $label ?? (__('app.upload') ?? '上传图片') }}</span>
        </label>
    @endif

    <input
        type="file"
        id="{{ $inputId }}"
        @if($accept) accept="{{ $accept }}" @endif
        @if($multiple) multiple @endif
        @if($wireDirective) {!! $wireDirective !!} @endif
        class="{{ $classes }} file:mr-4 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-100 file:text-teal-700 hover:file:bg-teal-100 file:transition-colors file:cursor-pointer"
        {{ $attributes->except(['wire','upload','preview','existing','conversion','accept','multiple','label','help','error','variant','previewSize','itemSize','showTempPreview','uploadingText']) }}
    />

    @if($wireTarget)
        <div wire:loading wire:target="{{ $wireTarget }}" class="text-xs text-gray-500">
            {{ $uploadingText }}
        </div>
    @endif

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
