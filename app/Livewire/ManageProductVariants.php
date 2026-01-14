<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Specification;
use App\Models\SpecificationValue;
use App\Services\LocaleCurrencyService;
use App\Services\ProductVariantService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageProductVariants extends Component
{
    use WithFileUploads;
    
    public $productId;
    public $product;
    
    // 规格选择
    public $selectedSpecifications = []; // [spec_id => [value_id1, value_id2, ...]]
    
    // SKU 数据
    public $skus = []; // [['specification_values' => [...], 'sku' => '', 'price' => '', 'image' => ..., ...], ...]
    
    // 批量操作
    public $bulkPrice = '';
    public $bulkCost = '';
    public $bulkStock = '';
    public $bulkStatus = 'active';
    public $showBulkActions = false;
    public $selectedSkus = [];
    
    // 编辑状态
    public $editingSkuIndex = null;
    
    protected $localeCurrencyService;
    protected $currency;
    protected ?ProductVariantService $variantService = null;

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->product = Product::with(['productVariants.specificationValues', 'productVariants.media'])->findOrFail($productId);
        
        $this->localeCurrencyService = app(LocaleCurrencyService::class);
        $this->currency = $this->localeCurrencyService->getCurrencyByCode(session('currency'));
        $this->variantService = app(ProductVariantService::class);
        
        // 加载现有的 SKU 数据
        $this->loadExistingVariants();
    }

    /**
     * 获取当前语言
     */
    protected function getCurrentLanguage()
    {
        if (!$this->localeCurrencyService) {
            $this->localeCurrencyService = app(LocaleCurrencyService::class);
        }
        $locale = app()->getLocale();
        return $this->localeCurrencyService->getLanguageByCode($locale);
    }

    /**
     * 加载现有的 SKU 数据
     */
    protected function loadExistingVariants()
    {
        // 清空现有数据，避免重复
        $this->selectedSpecifications = [];
        $this->skus = [];
        
        $variants = $this->product->productVariants()->with(['specificationValues', 'media'])->get();
        
            // 从现有 SKU 中提取已选择的规格值
        foreach ($variants as $variant) {
            $specValues = [];
            foreach ($variant->specificationValues as $sv) {
                // 从 pivot 中获取 specification_id（更准确）
                $specId = (string) ($sv->pivot->specification_id ?? $sv->specification_id);
                if (!isset($this->selectedSpecifications[$specId]) || !is_array($this->selectedSpecifications[$specId])) {
                    $this->selectedSpecifications[$specId] = [];
                }
                if (!in_array($sv->id, $this->selectedSpecifications[$specId])) {
                    $this->selectedSpecifications[$specId][] = (int) $sv->id;
                }
                $specValues[] = [
                    'specification_id' => (int) $specId,
                    'specification_value_id' => $sv->id,
                ];
            }
            
            // 按规格ID排序规格值
            usort($specValues, function($a, $b) {
                return $a['specification_id'] <=> $b['specification_id'];
            });
            
            // 获取SKU图片
            $imageUrl = null;
            $media = $variant->getFirstMedia('image');
            if ($media) {
                try {
                    // 先尝试获取缩略图 URL
                    $thumbUrl = $media->getUrl('thumb');
                    // 检查缩略图文件是否存在
                    $thumbPath = $media->getPath('thumb');
                    if ($thumbPath && file_exists($thumbPath)) {
                        $imageUrl = $thumbUrl;
                    } else {
                        // 如果缩略图不存在，使用原图
                        $imageUrl = $media->getUrl();
                    }
                } catch (\Exception $e) {
                    // 如果获取缩略图失败，使用原图
                    try {
                        $imageUrl = $media->getUrl();
                    } catch (\Exception $e2) {
                        // 如果原图也获取失败，设置为 null
                        $imageUrl = null;
                    }
                }
            }
            
            $this->skus[] = [
                'id' => $variant->id,
                'specification_values' => $specValues,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'cost' => $variant->cost,
                'stock' => $variant->stock,
                'status' => $this->product->status->value, // 使用商品的上下架状态
                'is_primary' => false, // 临时字段，不保存到数据库
                'sort_order' => 0, // 临时字段，用于排序
                'image' => null, // 文件上传对象
                'image_url' => $imageUrl, // 现有图片URL
            ];
        }
        
        // 如果已经有 SKU，不需要重新生成，只需要确保规格值选择正确
        // 如果没有 SKU，则根据选择的规格值生成
        if (empty($this->skus)) {
            $this->generateSkus();
        }
    }

    /**
     * 切换规格值选择
     */
    public function updatedSelectedSpecifications($value, $key)
    {
        // 确保值是数组
        if (!is_array($value)) {
            // 如果值不是数组，设置为空数组
            $this->selectedSpecifications[$key] = [];
            return;
        }
        
        // 确保数组中的值都是整数，并过滤掉空值和字符串
        $filteredValue = array_filter($value, function($v) {
            return $v !== null && $v !== '' && (is_numeric($v) || is_string($v));
        });
        
        // 转换为整数并重新索引数组
        $normalizedValue = array_values(array_map(function($v) {
            return is_string($v) ? (int) $v : (int) $v;
        }, $filteredValue));
        
        // 只有当值真正改变时才更新
        $currentValue = $this->selectedSpecifications[$key] ?? [];
        if ($normalizedValue !== $currentValue) {
            $this->selectedSpecifications[$key] = $normalizedValue;
        }
        // 注意：不再在这里调用 generateSkus()，由 wire:change 事件处理
    }

    /**
     * 切换规格值选择（备用方法）
     */
    public function toggleSpecificationValue($specId, $valueId)
    {
        $specId = (string) $specId;
        $valueId = (int) $valueId;
        
        if (!isset($this->selectedSpecifications[$specId])) {
            $this->selectedSpecifications[$specId] = [];
        }
        
        $index = array_search($valueId, $this->selectedSpecifications[$specId]);
        if ($index !== false) {
            unset($this->selectedSpecifications[$specId][$index]);
            $this->selectedSpecifications[$specId] = array_values($this->selectedSpecifications[$specId]);
        } else {
            $this->selectedSpecifications[$specId][] = $valueId;
        }
        
        // 重新生成 SKU
        $this->generateSkus();
    }

    /**
     * 生成 SKU 组合（笛卡尔积）
     */
    public function generateSkus()
    {
        // 获取所有规格，按 ID 排序（转换为整数进行排序）
        $specIds = array_keys($this->selectedSpecifications);
        usort($specIds, function($a, $b) {
            return (int) $a <=> (int) $b;
        });
        
        if (empty($specIds)) {
            $this->skus = [];
            return;
        }
        
        // 构建规格值数组，按规格顺序和规格值顺序
        $specValueArrays = [];
        foreach ($specIds as $specId) {
            $valueIds = $this->selectedSpecifications[$specId] ?? [];
            // 确保是数组且不为空
            if (!is_array($valueIds) || empty($valueIds)) {
                continue;
            }
            sort($valueIds); // 按规格值 ID 排序
            $specValueArrays[] = array_map(function($valueId) use ($specId) {
                return [
                    'specification_id' => (int) $specId,
                    'specification_value_id' => (int) $valueId,
                ];
            }, $valueIds);
        }
        
        // 如果没有任何规格值被选中，清空 SKU
        if (empty($specValueArrays)) {
            $this->skus = [];
            return;
        }
        
        // 确保 variantService 已初始化
        if (!$this->variantService) {
            $this->variantService = app(ProductVariantService::class);
        }
        
        // 生成笛卡尔积
        $combinations = $this->variantService->cartesianProduct($specValueArrays);
        
        // 清空旧的 SKU，生成全新的 SKU 数组
        $newSkus = [];
        foreach ($combinations as $index => $combination) {
            // 创建新的空 SKU
            $newSkus[] = [
                'id' => null,
                'specification_values' => $combination,
                'sku' => '',
                'price' => '',
                'cost' => '',
                'stock' => 0,
                'status' => $this->product->status->value,
                'is_primary' => false,
                'sort_order' => $index,
                'image' => null,
                'image_url' => null,
            ];
        }
        
        $this->skus = $newSkus;
    }


    /**
     * 根据规格值组合查找 SKU
     */
    protected function findSkuBySpecificationValues(array $specValues, array $skusToSearch = null, array $excludeIndices = []): ?int
    {
        if ($skusToSearch === null) {
            $skusToSearch = $this->skus;
        }
        
        foreach ($skusToSearch as $index => $sku) {
            // 跳过已使用的索引
            if (in_array($index, $excludeIndices)) {
                continue;
            }
            
            if (count($sku['specification_values']) !== count($specValues)) {
                continue;
            }
            
            $match = true;
            foreach ($specValues as $sv) {
                $found = false;
                foreach ($sku['specification_values'] as $existingSv) {
                    if ($existingSv['specification_id'] === $sv['specification_id'] 
                        && $existingSv['specification_value_id'] === $sv['specification_value_id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                return $index;
            }
        }
        
        return null;
    }

    /**
     * 更新 SKU 字段
     */
    public function updateSku($index, $field, $value)
    {
        if (!isset($this->skus[$index])) {
            return;
        }
        
        $this->skus[$index][$field] = $value;
        
        // 如果是主商品设置，确保只有一个
        if ($field === 'is_primary' && $value) {
            foreach ($this->skus as $i => $sku) {
                if ($i !== $index) {
                    $this->skus[$i]['is_primary'] = false;
                }
            }
        }
    }
    
    /**
     * 处理图片上传后的预览更新
     */
    public function updatedSkus($value, $key)
    {
        // 如果上传了图片，更新预览URL
        if (str_ends_with($key, '.image') && $value) {
            $parts = explode('.', $key);
            $index = (int) $parts[1];
            if (isset($this->skus[$index]) && $value) {
                try {
                    $this->skus[$index]['image_url'] = $value->temporaryUrl();
                } catch (\Exception $e) {
                    // 如果无法获取临时URL，使用空值
                    $this->skus[$index]['image_url'] = null;
                }
            }
        }
    }
    
    /**
     * 删除 SKU 图片
     */
    public function removeSkuImage($index)
    {
        if (!isset($this->skus[$index])) {
            return;
        }
        
        $this->skus[$index]['image'] = null;
        $this->skus[$index]['image_url'] = null;
        
        // 如果 SKU 已存在，立即删除数据库中的图片
        if ($this->skus[$index]['id']) {
            $variant = ProductVariant::find($this->skus[$index]['id']);
            if ($variant) {
                $variant->clearMediaCollection('image');
            }
        }
    }

    /**
     * 调整 SKU 顺序
     */
    public function moveSku($index, $direction)
    {
        if ($direction === 'up' && $index > 0) {
            $temp = $this->skus[$index];
            $this->skus[$index] = $this->skus[$index - 1];
            $this->skus[$index - 1] = $temp;
        } elseif ($direction === 'down' && $index < count($this->skus) - 1) {
            $temp = $this->skus[$index];
            $this->skus[$index] = $this->skus[$index + 1];
            $this->skus[$index + 1] = $temp;
        }
    }

    /**
     * 批量更新
     */
    public function applyBulkUpdate()
    {
        foreach ($this->selectedSkus as $index) {
            if (!isset($this->skus[$index])) {
                continue;
            }
            
            if ($this->bulkPrice !== '') {
                $this->skus[$index]['price'] = $this->bulkPrice;
            }
            if ($this->bulkCost !== '') {
                $this->skus[$index]['cost'] = $this->bulkCost;
            }
            if ($this->bulkStock !== '') {
                $this->skus[$index]['stock'] = $this->bulkStock;
            }
            if ($this->bulkStatus !== '') {
                $this->skus[$index]['status'] = $this->bulkStatus;
            }
        }
        
        $this->resetBulkActions();
    }

    /**
     * 重置批量操作
     */
    public function resetBulkActions()
    {
        $this->bulkPrice = '';
        $this->bulkCost = '';
        $this->bulkStock = '';
        $this->bulkStatus = 'active';
        $this->selectedSkus = [];
        $this->showBulkActions = false;
    }

    /**
     * 全选/取消全选所有 SKU
     * 当用户点击全选复选框时调用
     */
    public function toggleSelectAll()
    {
        // 切换批量操作状态
        $this->showBulkActions = !$this->showBulkActions;
        
        if ($this->showBulkActions) {
            // 如果开启批量操作，自动全选所有 SKU
            if (count($this->skus) > 0) {
                $this->selectedSkus = array_keys($this->skus);
            }
        } else {
            // 如果关闭批量操作，清空选中项
            $this->selectedSkus = [];
        }
    }

    /**
     * 当 showBulkActions 改变时（保留此方法以防其他地方直接修改 showBulkActions）
     */
    public function updatedShowBulkActions($value)
    {
        if ($value) {
            // 如果开启批量操作，自动全选所有 SKU
            if (count($this->skus) > 0 && empty($this->selectedSkus)) {
                $this->selectedSkus = array_keys($this->skus);
            }
        } else {
            // 如果关闭批量操作，清空选中项
            $this->selectedSkus = [];
        }
    }

    /**
     * 保存所有 SKU
     */
    public function saveAll()
    {
        // 验证所有 SKU 都有编码
        foreach ($this->skus as $index => $sku) {
            if (empty($sku['sku'])) {
                session()->flash('error', __('filament.product_variant_manage.sku_code_required'));
                return;
            }
        }
        
        // 检查 SKU 编码唯一性
        $skuCodes = [];
        foreach ($this->skus as $sku) {
            $skuCodes[] = $sku['sku'];
        }
        if (count($skuCodes) !== count(array_unique($skuCodes))) {
            session()->flash('error', __('filament.product_variant_manage.sku_code_unique'));
            return;
        }
        
        // 检查 SKU 编码是否与其他商品的 SKU 重复
        $existingSkus = ProductVariant::where('product_id', '!=', $this->productId)
            ->whereIn('sku', $skuCodes)
            ->pluck('sku')
            ->toArray();
        if (!empty($existingSkus)) {
            session()->flash('error', __('filament.product_variant_manage.sku_code_exists') . implode(', ', $existingSkus));
            return;
        }
        
        // 保存之前，先保存旧 SKU 的图片映射（规格值组合 -> 图片文件路径）
        $existingVariants = ProductVariant::where('product_id', $this->productId)
            ->with(['specificationValues', 'media'])
            ->get();
        
        $oldImageMap = []; // [规格值组合的key => ['path' => 临时文件路径, 'name' => 文件名, 'file_name' => 存储文件名]]
        $tempFiles = []; // 记录所有临时文件，确保最后都能被清理
        foreach ($existingVariants as $variant) {
            $media = $variant->getFirstMedia('image');
            if ($media) {
                $filePath = $media->getPath();
                if ($filePath && file_exists($filePath)) {
                    // 构建规格值组合的 key（使用 pivot 中的 specification_id）
                    // 确保与新 SKU 的 key 构建方式完全一致
                    $specValues = $variant->specificationValues->map(function($sv) {
                        // 从 pivot 中获取 specification_id（因为 relationship 已经加载了 pivot）
                        $specId = (int)($sv->pivot->specification_id ?? $sv->specification_id);
                        $valueId = (int)$sv->id;
                        return $specId . ':' . $valueId;
                    })->sort()->values() // 重新索引，确保排序后的数组格式一致
                    ->implode(',');
                    
                    // 复制文件到临时位置，避免删除时文件被清理
                    $tempDir = storage_path('app/temp');
                    if (!is_dir($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }
                    $tempPath = $tempDir . '/' . uniqid() . '_' . $media->file_name;
                    copy($filePath, $tempPath);
                    $tempFiles[] = $tempPath; // 记录临时文件
                    
                    $oldImageMap[$specValues] = [
                        'path' => $tempPath,
                        'name' => $media->name,
                        'file_name' => $media->file_name,
                    ];
                }
            }
        }
        
        // 删除所有现有 SKU（图片会在删除时自动清理，但我们已经在上面保存了文件路径）
        // 注意：ProductVariant 模型使用了 Syncable trait，delete() 操作会自动触发节点间同步
        foreach ($existingVariants as $variant) {
            $variant->delete(); // 自动触发 'deleted' 事件，Syncable trait 会调用 SyncService::recordSync()
        }
        
        // 创建新的 SKU
        // 注意：ProductVariant 模型使用了 Syncable trait，create() 操作会自动触发节点间同步
        foreach ($this->skus as $sku) {
            $pivotData = [];
            foreach ($sku['specification_values'] as $sv) {
                $pivotData[$sv['specification_value_id']] = [
                    'specification_id' => $sv['specification_id'],
                ];
            }
            
            // 构建规格值组合的 key（用于匹配旧图片）
            // 确保与旧图片映射的 key 构建方式完全一致
            $specValuesKey = collect($sku['specification_values'])
                ->map(function($sv) {
                    // 使用整数类型确保一致性
                    return (int)$sv['specification_id'] . ':' . (int)$sv['specification_value_id'];
                })
                ->sort()
                ->values() // 重新索引，确保排序后的数组格式一致
                ->implode(',');
            
            // 创建新 SKU
            // create() 会自动触发 'created' 事件，Syncable trait 会调用 SyncService::recordSync()
            $variant = ProductVariant::create([
                'product_id' => $this->productId,
                'sku' => $sku['sku'],
                'price' => $sku['price'] ?: null,
                'cost' => $sku['cost'] ?: null,
                'stock' => $sku['stock'] ?? 0,
            ]);
            // syncSpecificationValues() 方法内部会处理 pivot 表的同步（ProductVariantSpecificationValue）
            $variant->syncSpecificationValues($pivotData);
            
            // 处理图片上传
            if (isset($sku['image']) && $sku['image']) {
                // 有新图片，上传新图片
                $variant->addMedia($sku['image']->getRealPath())
                    ->usingName($sku['image']->getClientOriginalName())
                    ->usingFileName($sku['image']->getClientOriginalName())
                    ->toMediaCollection('image');
                $variant->load('media');
            } elseif (isset($oldImageMap[$specValuesKey])) {
                // 没有新图片，但有旧图片，从临时位置复制旧图片
                $oldImage = $oldImageMap[$specValuesKey];
                if (file_exists($oldImage['path'])) {
                    try {
                        $variant->addMedia($oldImage['path'])
                            ->usingName($oldImage['name'])
                            ->usingFileName($oldImage['file_name'])
                            ->toMediaCollection('image');
                        $variant->load('media');
                        // 标记已使用，稍后统一清理
                        $oldImageMap[$specValuesKey]['used'] = true;
                    } catch (\Exception $e) {
                        // 如果复制失败，记录错误但继续执行
                        \Log::error('Failed to copy old image for SKU', [
                            'sku' => $sku['sku'],
                            'specValuesKey' => $specValuesKey,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
        
        // 清理所有临时文件
        foreach ($oldImageMap as $key => $oldImage) {
            if (isset($oldImage['path']) && file_exists($oldImage['path'])) {
                // 无论是否使用，都删除临时文件（文件已经被复制到新位置）
                @unlink($oldImage['path']);
            }
        }
        // 清理 tempFiles 中可能遗漏的文件
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
        
        // 重新加载数据（包括新上传的图片）
        $this->product->refresh();
        $this->product->load(['productVariants.specificationValues', 'productVariants.media']);
        
        // 清空并重新加载 SKU 数据（loadExistingVariants 内部会清空，但这里显式清空更安全）
        $this->selectedSpecifications = [];
        $this->skus = [];
        $this->loadExistingVariants();
        
        session()->flash('success', __('filament.product_variant_manage.save_success'));
    }

    /**
     * 删除 SKU
     */
    public function deleteSku($index)
    {
        if (isset($this->skus[$index])) {
            $sku = $this->skus[$index];
            if ($sku['id']) {
                $variant = ProductVariant::find($sku['id']);
                if ($variant) {
                    $variant->delete();
                }
            }
            unset($this->skus[$index]);
            $this->skus = array_values($this->skus);
        }
    }

    /**
     * 获取规格列表
     */
    public function getSpecificationsProperty()
    {
        $lang = $this->getCurrentLanguage();
        $specs = Specification::with(['specificationTranslations', 'specificationValues.specificationValueTranslations'])
            ->orderBy('id')
            ->get();
        
        $result = [];
        foreach ($specs as $spec) {
            $translation = $spec->specificationTranslations->where('language_id', $lang?->id)->first();
            $specName = $translation && $translation->name
                ? $translation->name
                : ($spec->specificationTranslations->first()->name ?? $spec->id);
            
            $values = [];
            foreach ($spec->specificationValues->sortBy('id') as $sv) {
                $valTrans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                $valName = $valTrans && $valTrans->name
                    ? $valTrans->name
                    : ($sv->specificationValueTranslations->first()->name ?? $sv->id);
                
                $specIdKey = (string) $spec->id;
                $selectedValues = $this->selectedSpecifications[$specIdKey] ?? [];
                // 确保 selectedValues 是数组
                if (!is_array($selectedValues)) {
                    $selectedValues = [];
                }
                
                $values[] = [
                    'id' => $sv->id,
                    'name' => $valName,
                    'selected' => in_array($sv->id, $selectedValues),
                ];
            }
            
            $result[] = [
                'id' => $spec->id,
                'name' => $specName,
                'values' => $values,
            ];
        }
        
        return $result;
    }

    /**
     * 获取已选中规格值的规格列表（用于表格显示）
     */
    public function getSelectedSpecificationsForTableProperty()
    {
        $lang = $this->getCurrentLanguage();
        $specs = Specification::with(['specificationTranslations', 'specificationValues.specificationValueTranslations'])
            ->orderBy('id')
            ->get();
        
        $result = [];
        foreach ($specs as $spec) {
            $specIdKey = (string) $spec->id;
            $selectedValues = $this->selectedSpecifications[$specIdKey] ?? [];
            
            // 只包含有选中值的规格
            if (!is_array($selectedValues) || empty($selectedValues)) {
                continue;
            }
            
            $translation = $spec->specificationTranslations->where('language_id', $lang?->id)->first();
            $specName = $translation && $translation->name
                ? $translation->name
                : ($spec->specificationTranslations->first()->name ?? $spec->id);
            
            $values = [];
            foreach ($spec->specificationValues->sortBy('id') as $sv) {
                $valTrans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                $valName = $valTrans && $valTrans->name
                    ? $valTrans->name
                    : ($sv->specificationValueTranslations->first()->name ?? $sv->id);
                
                $values[] = [
                    'id' => $sv->id,
                    'name' => $valName,
                    'selected' => in_array($sv->id, $selectedValues),
                ];
            }
            
            $result[] = [
                'id' => $spec->id,
                'name' => $specName,
                'values' => $values,
            ];
        }
        
        return $result;
    }

    /**
     * 获取合并单元格的行数（适用于所有规格列）
     */
    public function getRowspanForSpecColumn($index, $specId)
    {
        if (empty($this->skus) || $index >= count($this->skus)) {
            return 1;
        }
        
        // 获取当前行的指定规格值 ID
        $currentSpecValue = collect($this->skus[$index]['specification_values'])->firstWhere('specification_id', $specId);
        $currentValueId = $currentSpecValue['specification_value_id'] ?? null;
        
        if ($currentValueId === null) {
            return 1;
        }
        
        // 计算相同规格值的连续行数
        $rowspan = 1;
        
        for ($i = $index + 1; $i < count($this->skus); $i++) {
            $nextSpecValue = collect($this->skus[$i]['specification_values'])->firstWhere('specification_id', $specId);
            $nextValueId = $nextSpecValue['specification_value_id'] ?? null;
            
            if ($nextValueId === $currentValueId) {
                $rowspan++;
            } else {
                break;
            }
        }
        
        return $rowspan;
    }

    /**
     * 检查是否应该显示规格列单元格
     */
    public function shouldShowSpecColumnCell($index, $specId)
    {
        if ($index === 0) {
            return true;
        }
        
        if ($index >= count($this->skus)) {
            return false;
        }
        
        $currentSpecValue = collect($this->skus[$index]['specification_values'])->firstWhere('specification_id', $specId);
        $currentValueId = $currentSpecValue['specification_value_id'] ?? null;
        
        $prevSpecValue = collect($this->skus[$index - 1]['specification_values'])->firstWhere('specification_id', $specId);
        $prevValueId = $prevSpecValue['specification_value_id'] ?? null;
        
        return $currentValueId !== $prevValueId;
    }

    /**
     * 获取规格值名称
     */
    public function getSpecificationValueName($specId, $valueId)
    {
        $lang = $this->getCurrentLanguage();
        $spec = Specification::with(['specificationValues.specificationValueTranslations'])->find($specId);
        if (!$spec) {
            return $valueId;
        }
        
        $sv = $spec->specificationValues->where('id', $valueId)->first();
        if (!$sv) {
            return $valueId;
        }
        
        $translation = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
        return $translation && $translation->name
            ? $translation->name
            : ($sv->specificationValueTranslations->first()->name ?? $valueId);
    }

    public function render()
    {
        return view('livewire.manage-product-variants', [
            'specifications' => $this->specifications,
            'selectedSpecificationsForTable' => $this->selectedSpecificationsForTable,
            'currencySymbol' => $this->currency->symbol ?? '',
        ]);
    }
}
