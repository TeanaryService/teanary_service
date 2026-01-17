<div class="space-y-6">
    <!-- 规格选择区域 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">{{ __('filament.product_variant_manage.specification_selection') }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($specifications as $spec)
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium mb-3 text-gray-700">{{ $spec['name'] }}</h3>
                    <div class="space-y-2">
                        @foreach($spec['values'] as $value)
                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                <input 
                                    type="checkbox" 
                                    wire:click="toggleSpecificationValue({{ $spec['id'] }}, {{ $value['id'] }})"
                                    @if(isset($selectedSpecifications[$spec['id']]) && in_array($value['id'], $selectedSpecifications[$spec['id']]))
                                        checked
                                    @endif
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    wire:key="spec-{{ $spec['id'] }}-value-{{ $value['id'] }}"
                                >
                                <span class="text-sm text-gray-700">{{ $value['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SKU 表格区域 -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">{{ __('filament.product_variant_manage.sku_management') }}</h2>
            <div class="flex space-x-2">
                <button 
                    wire:click="saveAll"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                >
                    {{ __('filament.product_variant_manage.save_all') }}
                </button>
            </div>
        </div>

        <!-- 批量操作区域 -->
        @if($showBulkActions && count($selectedSkus) > 0)
            <div class="p-4 bg-gray-50 border-b">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('filament.product_variant_manage.bulk_set_price') }}</label>
                        <input 
                            type="number" 
                            step="0.01"
                            wire:model="bulkPrice"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="{{ __('filament.product_variant_manage.leave_empty_no_change') }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('filament.product_variant_manage.bulk_set_cost') }}</label>
                        <input 
                            type="number" 
                            step="0.01"
                            wire:model="bulkCost"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="{{ __('filament.product_variant_manage.leave_empty_no_change') }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('filament.product_variant_manage.bulk_set_stock') }}</label>
                        <input 
                            type="number" 
                            wire:model="bulkStock"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="{{ __('filament.product_variant_manage.leave_empty_no_change') }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('filament.product_variant_manage.bulk_set_status') }}</label>
                        <select 
                            wire:model="bulkStatus"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="active">{{ __('filament.product_variant_manage.status_active') }}</option>
                            <option value="inactive">{{ __('filament.product_variant_manage.status_inactive') }}</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button 
                            wire:click="applyBulkUpdate"
                            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                        >
                            {{ __('filament.product_variant_manage.apply') }}
                        </button>
                        <button 
                            wire:click="resetBulkActions"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                        >
                            {{ __('filament.product_variant_manage.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- SKU 表格 -->
        <div class="overflow-x-auto w-full">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                            <input 
                                type="checkbox" 
                                wire:click="toggleSelectAll"
                                @if($showBulkActions) checked @endif
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                        </th>
                        @foreach($selectedSpecificationsForTable as $index => $spec)
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ $spec['name'] }}
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant.image') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant_manage.sku_code') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant_manage.sale_price') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant_manage.cost_price') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant_manage.stock') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant_manage.status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant_manage.primary_product') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('filament.product_variant_manage.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($skus as $index => $sku)
                        <tr class="hover:bg-gray-50" wire:key="sku-row-{{ $index }}">
                            <!-- 复选框 -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                @if($showBulkActions)
                                    <input 
                                        type="checkbox" 
                                        wire:model="selectedSkus"
                                        value="{{ $index }}"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    >
                                @endif
                            </td>
                            
                            <!-- 规格值列（所有列都支持合并单元格） -->
                            @foreach($selectedSpecificationsForTable as $specIndex => $spec)
                                @php
                                    $specValue = collect($sku['specification_values'])->firstWhere('specification_id', $spec['id']);
                                    $specValueName = $specValue ? $this->getSpecificationValueName($specValue['specification_id'], $specValue['specification_value_id']) : '-';
                                    $shouldShow = $this->shouldShowSpecColumnCell($index, $spec['id']);
                                    $rowspan = $this->getRowspanForSpecColumn($index, $spec['id']);
                                @endphp
                                
                                @if($shouldShow)
                                    <td 
                                        class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 align-middle"
                                        @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif
                                    >
                                        {{ $specValueName }}
                                    </td>
                                @endif
                            @endforeach
                            
                            <!-- SKU图片 -->
                            <td class="px-4 py-3 align-middle">
                                <div 
                                    x-data="{ 
                                        isDragging: false,
                                        preview: @if(isset($sku['image_url']) && $sku['image_url']) '{{ $sku['image_url'] }}' @else null @endif,
                                        hasImage: {{ isset($sku['image_url']) && $sku['image_url'] ? 'true' : 'false' }},
                                        handleFileSelect(event) {
                                            const file = event.target.files[0];
                                            if (file && file.type.startsWith('image/')) {
                                                @this.set('skus.{{ $index }}.image', file);
                                                const reader = new FileReader();
                                                reader.onload = (e) => {
                                                    this.preview = e.target.result;
                                                    this.hasImage = true;
                                                };
                                                reader.readAsDataURL(file);
                                            }
                                        },
                                        handleDrop(event) {
                                            event.preventDefault();
                                            this.isDragging = false;
                                            const file = event.dataTransfer.files[0];
                                            if (file && file.type.startsWith('image/')) {
                                                @this.set('skus.{{ $index }}.image', file);
                                                const reader = new FileReader();
                                                reader.onload = (e) => {
                                                    this.preview = e.target.result;
                                                    this.hasImage = true;
                                                };
                                                reader.readAsDataURL(file);
                                            }
                                        },
                                        removeImage() {
                                            @this.call('removeSkuImage', {{ $index }});
                                            this.preview = null;
                                            this.hasImage = false;
                                        }
                                    }"
                                    x-effect="
                                        const sku = $wire.skus[{{ $index }}];
                                        if (sku) {
                                            if (sku.image_url) {
                                                preview = sku.image_url;
                                                hasImage = true;
                                            } else {
                                                preview = null;
                                                hasImage = false;
                                            }
                                        } else {
                                            preview = null;
                                            hasImage = false;
                                        }
                                    "
                                    class="relative"
                                    wire:key="sku-image-{{ $index }}"
                                >
                                    <!-- 图片预览区域 -->
                                    <div 
                                        class="relative w-20 h-20 mx-auto rounded-lg overflow-hidden border-2 transition-all duration-200"
                                        :class="hasImage ? 'border-primary-300 shadow-md' : 'border-dashed border-gray-300'"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleDrop($event)"
                                    >
                                        <!-- 现有图片或预览 -->
                                        <template x-if="hasImage && preview">
                                            <div class="relative w-full h-full">
                                                <img 
                                                    :src="preview" 
                                                    alt="SKU Image" 
                                                    class="w-full h-full object-cover"
                                                >
                                                <!-- 删除按钮 -->
                                                <button
                                                    type="button"
                                                    @click="removeImage()"
                                                    class="absolute top-0 right-0 px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg shadow-lg hover:bg-red-700 transition-all duration-200 flex items-center justify-center gap-1.5 z-10 transform hover:scale-105"
                                                    title="{{ __('filament.product_variant_manage.delete') }}"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    <span>{{ __('filament.product_variant_manage.delete') }}</span>
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <!-- 空状态 -->
                                        <template x-if="!hasImage">
                                            <label 
                                                for="sku-image-{{ $index }}"
                                                class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition-colors duration-200"
                                                :class="isDragging ? 'bg-primary-50 border-primary-400' : ''"
                                            >
                                                <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span class="text-xs text-gray-500 text-center px-1">点击上传</span>
                                            </label>
                                        </template>
                                        
                                        <!-- 文件输入 -->
                                        <input 
                                            type="file" 
                                            wire:model.live="skus.{{ $index }}.image"
                                            accept="image/*"
                                            @change="handleFileSelect($event)"
                                            class="hidden"
                                            id="sku-image-{{ $index }}"
                                        >
                                    </div>
                                    
                                    <!-- 上传状态提示 -->
                                    @if(isset($skus[$index]['image']) && $skus[$index]['image'])
                                        <div class="mt-2 text-xs text-center">
                                            <div class="inline-flex items-center px-2 py-1 bg-primary-50 text-primary-700 rounded-md">
                                                <svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>{{ $skus[$index]['image']->getClientOriginalName() }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- 拖拽提示 -->
                                    <div 
                                        x-show="isDragging"
                                        class="absolute inset-0 bg-primary-100 bg-opacity-90 rounded-lg flex items-center justify-center z-10"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                    >
                                        <div class="text-center">
                                            <svg class="w-12 h-12 text-primary-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <p class="text-sm font-medium text-primary-700">释放以上传</p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- SKU编码 -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <input 
                                    type="text" 
                                    wire:model.live="skus.{{ $index }}.sku"
                                    class="w-32 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    placeholder="{{ __('filament.product_variant_manage.sku_code') }}"
                                >
                            </td>
                            
                            <!-- 销售价格 -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500 mr-1">{{ $currencySymbol }}</span>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        wire:model.live="skus.{{ $index }}.price"
                                        class="w-24 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        placeholder="0.00"
                                    >
                                </div>
                            </td>
                            
                            <!-- 成本价 -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500 mr-1">{{ $currencySymbol }}</span>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        wire:model.live="skus.{{ $index }}.cost"
                                        class="w-24 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        placeholder="0.00"
                                    >
                                </div>
                            </td>
                            
                            <!-- 库存 -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <input 
                                    type="number" 
                                    wire:model.live="skus.{{ $index }}.stock"
                                    class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    placeholder="0"
                                >
                            </td>
                            
                            <!-- 上下架状态 -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <select 
                                    wire:model.live="skus.{{ $index }}.status"
                                    class="w-24 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                >
                                    <option value="active">{{ __('filament.product_variant_manage.status_active') }}</option>
                                    <option value="inactive">{{ __('filament.product_variant_manage.status_inactive') }}</option>
                                </select>
                            </td>
                            
                            <!-- 主商品设置 -->
                            <td class="px-4 py-3 whitespace-nowrap text-center align-middle">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="skus.{{ $index }}.is_primary"
                                    wire:change="updateSku({{ $index }}, 'is_primary', $event.target.checked)"
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                >
                            </td>
                            
                            <!-- 操作 -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <div class="flex space-x-2">
                                    @if($index > 0)
                                        <button 
                                            wire:click="moveSku({{ $index }}, 'up')"
                                            class="px-3 py-2 text-lg font-semibold text-primary-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg border border-primary-200"
                                            title="{{ __('filament.product_variant_manage.move_up') }}"
                                        >
                                            ↑
                                        </button>
                                    @endif
                                    @if($index < count($skus) - 1)
                                        <button 
                                            wire:click="moveSku({{ $index }}, 'down')"
                                            class="px-3 py-2 text-lg font-semibold text-primary-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg border border-primary-200"
                                            title="{{ __('filament.product_variant_manage.move_down') }}"
                                        >
                                            ↓
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="deleteSku({{ $index }})"
                                        wire:confirm="{{ __('filament.product_variant_manage.confirm_delete') }}"
                                        class="px-3 py-2 text-lg font-semibold text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg border border-red-200"
                                        title="{{ __('filament.product_variant_manage.delete') }}"
                                    >
                                        ×
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($selectedSpecificationsForTable) + 10 }}" class="px-4 py-8 text-center text-gray-500">
                                {{ __('filament.product_variant_manage.select_spec_values_first') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 消息提示 -->
    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</div>
