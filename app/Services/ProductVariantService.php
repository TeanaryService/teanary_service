<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class ProductVariantService
{
    /**
     * 计算笛卡尔积
     */
    public function cartesianProduct(array $arrays): array
    {
        if (empty($arrays)) {
            return [[]];
        }
        
        $result = [[]];
        foreach ($arrays as $array) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($array as $item) {
                    $newResult[] = array_merge($existing, [$item]);
                }
            }
            $result = $newResult;
        }
        
        return $result;
    }

    /**
     * 根据规格值组合找到对应的 SKU
     */
    public function findVariantBySpecificationValues(Collection $variants, array $specValueIds): ?int
    {
        $sortedSpecValueIds = collect($specValueIds)->sort()->values()->toArray();
        
        foreach ($variants as $variant) {
            $variantSpecValueIds = $variant->specificationValues
                ->map(function ($sv) {
                    return $sv->id;
                })
                ->sort()
                ->values()
                ->toArray();
            
            if ($variantSpecValueIds === $sortedSpecValueIds) {
                return $variant->id;
            }
        }
        
        return null;
    }

    /**
     * 检查规格值组合是否在 SKU 中存在（部分匹配）
     */
    public function hasMatchingVariant(Collection $variants, array $specValueIds): bool
    {
        foreach ($variants as $variant) {
            $variantSpecValueIds = $variant->specificationValues
                ->map(function ($sv) {
                    return $sv->id;
                })
                ->toArray();
            
            // 检查测试组合中的所有规格值是否都在这个 SKU 中
            $allMatch = true;
            foreach ($specValueIds as $testValueId) {
                if (!in_array($testValueId, $variantSpecValueIds)) {
                    $allMatch = false;
                    break;
                }
            }
            
            if ($allMatch) {
                return true;
            }
        }
        
        return false;
    }
}
