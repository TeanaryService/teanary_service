<div class="space-y-6">
    {{-- 规格选择 --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('manager.product_variants.specification_values') }}
        </h2>
        <p class="text-xs text-gray-500 mb-2">
            {{ __('manager.product_variants.manage.save_product_first') }}
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($specifications as $spec)
                <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">{{ $spec['name'] }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($spec['values'] as $value)
                            @php
                                $selectedValues = $selectedSpecifications[$spec['id']] ?? [];
                                $isSelected = is_array($selectedValues) && in_array($value['id'], $selectedValues, true);
                            @endphp
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border cursor-pointer
                                {{ $isSelected ? 'bg-teal-100 text-teal-700 border-teal-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                                <x-widgets.checkbox 
                                    wire:click="toggleSpecificationValue({{ $spec['id'] }}, {{ $value['id'] }})"
                                    :checked="$isSelected"
                                    :label="$value['name']"
                                    class="!gap-1"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SKU 表格 --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold text-gray-900">
                {{ __('manager.product_variants.plural_label') }}
            </h2>
            <div class="flex items-center gap-3">
                <x-widgets.button 
                    type="button"
                    wire:click="regenerateSkus"
                    variant="secondary"
                    class="px-3 py-1.5 text-xs"
                >
                    {{ __('manager.product_variants.manage.regenerate') ?? '重新生成 SKU' }}
                </x-widgets.button>
                <x-widgets.button 
                    type="button"
                    wire:click="saveAll"
                    class="px-3 py-1.5 text-xs"
                >
                    {{ __('manager.product_variants.manage.save') ?? '保存全部 SKU' }}
                </x-widgets.button>
            </div>
        </div>

        @if (session()->has('error'))
            <div class="mb-3 rounded-md bg-red-50 p-3 text-xs text-red-700">
                {{ session('error') }}
            </div>
        @endif
        @if (session()->has('success'))
            <div class="mb-3 rounded-md bg-teal-100 p-3 text-xs text-teal-800">
                {{ session('success') }}
            </div>
        @endif

        @if(empty($skus))
            <p class="text-sm text-gray-500">
                {{ __('manager.product_variants.manage.select_specs_first') ?? '请先在上方选择规格和值，将自动生成 SKU 组合。' }}
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('manager.product_variants.specification_values') }}
                            </th>
                            <th class="px-3 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('manager.product_variants.sku') }}
                            </th>
                            <th class="px-3 py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('manager.product_variants.price') }}
                            </th>
                            <th class="px-3 py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('manager.product_variants.cost') }}
                            </th>
                            <th class="px-3 py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('manager.product_variants.stock') }}
                            </th>
                            <th class="px-3 py-2 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('manager.product_variants.image') }}
                            </th>
                            <th class="px-3 py-2 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($skus as $index => $row)
                            @php
                                // 关键：给每行一个稳定的 key，避免删除/重排后 Livewire DOM 复用错误
                                $specKey = collect($row['specification_values'] ?? [])
                                    ->map(fn ($sv) => ((int) ($sv['specification_id'] ?? 0)).':'.((int) ($sv['specification_value_id'] ?? 0)))
                                    ->sort()
                                    ->values()
                                    ->implode(',');
                                $rowKey = !empty($row['id']) ? ('id-'.$row['id']) : ('spec-'.md5($specKey));
                            @endphp
                            <tr wire:key="sku-row-{{ $rowKey }}">
                                <td class="px-3 py-2 align-top">
                                    @php
                                        $specGroups = collect($row['specification_values'])->groupBy('specification_id');
                                    @endphp
                                    <div class="space-y-1">
                                        @foreach($specGroups as $specId => $values)
                                            @php
                                                $spec = \App\Models\Specification::with('specificationTranslations')->find($specId);
                                                $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());
                                                $specTrans = $spec?->specificationTranslations->where('language_id', $lang?->id)->first();
                                                $specName = $specTrans?->name ?? $spec?->specificationTranslations->first()->name ?? $specId;
                                            @endphp
                                            <div class="flex gap-1 flex-wrap">
                                                <span class="font-medium text-gray-700">{{ $specName }}:</span>
                                                @foreach($values as $v)
                                                    @php
                                                        $sv = \App\Models\SpecificationValue::with('specificationValueTranslations')->find($v['specification_value_id']);
                                                        $valTrans = $sv?->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                                        $valName = $valTrans?->name ?? $sv?->specificationValueTranslations->first()->name ?? $v['specification_value_id'];
                                                    @endphp
                                                    <span class="text-gray-700">{{ $valName }}</span>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <x-widgets.input 
                                        type="text"
                                        wire="skus.{{ $index }}.sku"
                                        class="text-xs rounded"
                                    />
                                </td>
                                <td class="px-3 py-2 align-top text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="text-gray-500">{{ $currencySymbol }}</span>
                                        <x-widgets.input 
                                            type="number" 
                                            step="0.01"
                                            wire="skus.{{ $index }}.price"
                                            class="w-20 text-xs text-right rounded"
                                        />
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="text-gray-500">{{ $currencySymbol }}</span>
                                        <x-widgets.input 
                                            type="number" 
                                            step="0.01"
                                            wire="skus.{{ $index }}.cost"
                                            class="w-20 text-xs text-right rounded"
                                        />
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top text-right">
                                    <x-widgets.input 
                                        type="number" 
                                        step="1"
                                        wire="skus.{{ $index }}.stock"
                                        class="w-16 text-xs text-right rounded"
                                    />
                                </td>
                                <td class="px-3 py-2 align-top text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        @if($row['image_url'])
                                            <div class="relative">
                                                <img src="{{ $row['image_url'] }}" alt="" class="w-10 h-10 rounded object-cover border">
                                                <button
                                                    type="button"
                                                    class="absolute -top-2 -right-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-white border border-red-200 text-red-600 hover:bg-red-50 shadow-sm"
                                                    wire:click="removeSkuImage({{ $index }})"
                                                    wire:confirm="{{ __('app.confirm_delete') }}"
                                                    title="{{ __('app.delete') }}"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file"
                                               wire:model="skus.{{ $index }}.image"
                                               class="block w-full text-[11px] text-gray-700 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top text-center">
                                    <button 
                                        type="button"
                                        wire:click="deleteVariant({{ $index }})"
                                        wire:confirm="{{ __('app.confirm_delete') }}"
                                        class="text-red-600 hover:text-red-700 text-xs font-medium transition-colors inline-flex items-center justify-center"
                                        title="{{ __('app.delete') }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

