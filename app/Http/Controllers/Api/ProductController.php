<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\HandlesApiResponses;
use App\Http\Controllers\Api\Concerns\HandlesApiTransactions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    use HandlesApiResponses, HandlesApiTransactions;

    public function __construct(
        protected ProductService $productService
    ) {}

    public function store(StoreProductRequest $request): JsonResponse
    {
        // 记录请求参数（排除大的图片内容）
        $manager = $request->get('authenticated_manager');
        $logData = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'manager_id' => $manager?->id,
            'manager_name' => $manager?->name,
            'slug' => $request->slug,
            'source_url' => $request->source_url,
            'main_image' => $request->main_image ? [
                'image_id' => $request->main_image['image_id'] ?? null,
                'has_contents' => isset($request->main_image['contents']),
                'has_image_url' => isset($request->main_image['image_url']),
                'contents_length' => isset($request->main_image['contents']) ? strlen($request->main_image['contents']) : null,
            ] : null,
            'main_images_count' => $request->main_images ? count($request->main_images) : 0,
            'content_images_count' => $request->content_images ? count($request->content_images) : 0,
            'content_images' => $request->content_images ? array_map(function ($img) {
                return [
                    'image_id' => $img['image_id'] ?? null,
                    'has_contents' => isset($img['contents']),
                    'has_image_url' => isset($img['image_url']),
                    'contents_length' => isset($img['contents']) ? strlen($img['contents']) : null,
                ];
            }, $request->content_images) : [],
            'translations_count' => $request->translations ? count($request->translations) : 0,
            'translations' => $request->translations ? array_map(function ($trans) {
                return [
                    'language_id' => $trans['language_id'] ?? null,
                    'name' => $trans['name'] ?? null,
                    'has_description' => isset($trans['description']),
                    'has_short_description' => isset($trans['short_description']),
                ];
            }, $request->translations) : [],
            'categories_count' => $request->categories ? count($request->categories) : 0,
            'categories' => $request->categories ? array_map(function ($cat) {
                return [
                    'slug' => $cat['slug'] ?? null,
                    'parent_id' => $cat['parent_id'] ?? null,
                ];
            }, $request->categories) : [],
            'variants_count' => $request->variants ? count($request->variants) : 0,
            'variants' => $request->variants ? array_map(function ($variant) {
                return [
                    'sku' => $variant['sku'] ?? null,
                    'price' => $variant['price'] ?? null,
                    'stock' => $variant['stock'] ?? null,
                    'weight' => $variant['weight'] ?? null,
                    'length' => $variant['length'] ?? null,
                    'width' => $variant['width'] ?? null,
                    'height' => $variant['height'] ?? null,
                    'specification_values_count' => isset($variant['specification_values']) ? count($variant['specification_values']) : 0,
                ];
            }, $request->variants) : [],
            'attributes_count' => $request->has('attributes') && is_array($request->input('attributes')) ? count($request->input('attributes')) : 0,
            'attributes' => $request->has('attributes') && is_array($request->input('attributes')) ? array_map(function ($attr) {
                return [
                    'name' => $attr['name'] ?? null,
                    'value' => $attr['value'] ?? null,
                ];
            }, $request->input('attributes')) : [],
        ];

        Log::info('商品上传接口收到请求', $logData);

        $openedTransaction = false;

        try {
            // 开始事务
            $openedTransaction = $this->beginTransactionIfNotInOne();

            // 创建商品
            $productData = [
                'slug' => $request->slug,
                'source_url' => $request->source_url,
                'content_images' => $request->content_images,
                'translations' => $request->translations,
                'categories' => $request->categories,
                'variants' => $request->variants,
                'attributes' => $request->input('attributes', []),
            ];

            // 支持 main_image（单个）或 main_images（数组）
            if ($request->has('main_images') && is_array($request->main_images)) {
                $productData['main_images'] = $request->main_images;
            } elseif ($request->has('main_image')) {
                $productData['main_image'] = $request->main_image;
            }

            $product = $this->productService->createProduct($productData);

            // 提交事务
            $this->commitIfOpened($openedTransaction);

            return $this->successResponse('商品创建成功', $product);
        } catch (\Exception $e) {
            $this->rollbackIfOpened($openedTransaction);

            return $this->handleException($e, '商品创建失败', [
                'request' => $request->all(),
            ]);
        }
    }
}
