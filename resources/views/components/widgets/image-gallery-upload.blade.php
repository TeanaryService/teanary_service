@props([
    'existing' => [],
    'uploads' => [],
    'wire' => null,
    'accept' => 'image/*',
    'label' => null,
    'help' => null,
    'error' => null,
    'conversion' => 'thumb',
    'uploadingText' => '正在上传图片…',
    'showTempPreview' => true,
])

{{-- 兼容旧组件名：内部统一转到 image-upload（后续可删除此文件） --}}
<x-widgets.image-upload
    :existing="$existing"
    :upload="$uploads"
    :wire="$wire"
    :accept="$accept"
    :multiple="true"
    :label="$label"
    :help="$help"
    :error="$error"
    :conversion="$conversion"
    :uploadingText="$uploadingText"
    :showTempPreview="$showTempPreview"
/>
