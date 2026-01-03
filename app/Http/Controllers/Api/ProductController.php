<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request): JsonResponse
    {
        // 检查是否已经在事务中（测试环境可能已经开启事务）
        $alreadyInTransaction = DB::transactionLevel() > 0;
        
        try {
            if (!$alreadyInTransaction) {
                DB::beginTransaction();
            }

            // 1. 创建商品基础信息
            $product = Product::create([
                'slug' => $request->slug,
            ]);

            // 2. 处理主图
            if ($request->has('main_image.contents')) {
                $imageContent = base64_decode($request->main_image['contents'], true);
                if ($imageContent === false) {
                    throw new \InvalidArgumentException('无效的 base64 图片数据');
                }
                $product->addMediaFromString($imageContent)
                    ->usingFileName($request->main_image['image_id'].'.png')
                    ->toMediaCollection('image');
            }

            // 3. 处理内容图片
            $imageMap = [];
            if ($request->has('content_images')) {
                foreach ($request->content_images as $image) {
                    $imageContent = base64_decode($image['contents'], true);
                    if ($imageContent === false) {
                        throw new \InvalidArgumentException('无效的 base64 图片数据');
                    }
                    $mediaItem = $product->addMediaFromString($imageContent)
                        ->usingFileName($image['image_id'].'.png')
                        ->toMediaCollection('content-images');

                    $imageMap[$image['image_id']] = $mediaItem->getUrl();
                }
            }

            // 4. 处理分类（如果分类不存在，自动创建）
            $categoryIds = [];
            if ($request->has('categories') && is_array($request->categories)) {
                foreach ($request->categories as $categoryData) {
                    $category = $this->findOrCreateCategory($categoryData);
                    
                    if ($category) {
                        $categoryIds[] = $category->id;
                    }
                }
            }

            // 关联商品和分类
            if (!empty($categoryIds)) {
                $product->productCategories()->syncWithoutDetaching($categoryIds);
            }

            // 5. 处理商品翻译内容
            foreach ($request->translations as $translation) {
                $description = $translation['description'] ?? null;

                // 替换内容中的图片占位符
                if ($description) {
                    foreach ($imageMap as $imageId => $url) {
                        $url = '/storage'.Str::of($url)->after('/storage');
                        $description = str_replace(
                            '{{image:'.$imageId.'}}',
                            $url,
                            $description
                        );
                    }
                }

                $product->productTranslations()->create([
                    'language_id' => $translation['language_id'],
                    'name' => $translation['name'],
                    'description' => $description,
                    'short_description' => $translation['short_description'] ?? null,
                ]);
            }

            // 6. 处理商品规格（ProductVariant）
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $variantData) {
                    $variant = $product->productVariants()->create([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'] ?? null,
                        'cost' => $variantData['cost'] ?? null,
                        'stock' => $variantData['stock'] ?? 0,
                        'weight' => $variantData['weight'] ?? null,
                        'length' => $variantData['length'] ?? null,
                        'width' => $variantData['width'] ?? null,
                        'height' => $variantData['height'] ?? null,
                    ]);

                    // 关联规格值
                    if (isset($variantData['specification_values']) && is_array($variantData['specification_values'])) {
                        $syncData = [];
                        foreach ($variantData['specification_values'] as $specValue) {
                            $syncData[$specValue['specification_value_id']] = [
                                'specification_id' => $specValue['specification_id'],
                            ];
                        }
                        $variant->specificationValues()->sync($syncData);
                    }
                }
            }

            if (!$alreadyInTransaction) {
                DB::commit();
            }

            // 手动触发索引
            $product->searchable();

            return response()->json([
                'message' => '商品创建成功',
                'data' => $product->load([
                    'productTranslations',
                    'media',
                    'productCategories',
                    'productVariants.specificationValues',
                ]),
            ], 201);
        } catch (\Exception $e) {
            if (!$alreadyInTransaction) {
                DB::rollBack();
            }
            Log::error('商品创建失败：'.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => '商品创建失败',
                'error' => config('app.debug') ? $e->getMessage() : '系统错误',
            ], 500);
        }
    }

    /**
     * 查找或创建分类
     *
     * @param array $categoryData
     * @return Category|null
     */
    private function findOrCreateCategory(array $categoryData): ?Category
    {
        // 根据slug查找分类
        $category = Category::where('slug', $categoryData['slug'])->first();

        // 如果分类不存在，创建分类
        if (!$category) {
            $category = Category::create([
                'slug' => $categoryData['slug'],
                'parent_id' => $categoryData['parent_id'] ?? null,
            ]);
        }

        // 处理分类的多语言翻译
        if (isset($categoryData['translations']) && is_array($categoryData['translations'])) {
            foreach ($categoryData['translations'] as $translation) {
                // 检查该语言的翻译是否已存在
                $existingTranslation = CategoryTranslation::where('category_id', $category->id)
                    ->where('language_id', $translation['language_id'])
                    ->first();

                // 如果翻译不存在，创建翻译
                if (!$existingTranslation) {
                    CategoryTranslation::create([
                        'category_id' => $category->id,
                        'language_id' => $translation['language_id'],
                        'name' => $translation['name'],
                        'description' => $translation['description'] ?? null,
                    ]);

                    // 清除分类缓存
                    Cache::forget('categories.with.translations');
                }
            }
        }

        return $category;
    }
}
