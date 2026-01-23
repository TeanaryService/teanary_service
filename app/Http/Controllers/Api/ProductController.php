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
        // 直接记录原始请求数据
        $manager = $request->get('authenticated_manager');
        Log::info('商品上传接口收到请求', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'manager_id' => $manager?->id,
            'manager_name' => $manager?->name,
            'request_data' => $request->all(),
        ]);

        $openedTransaction = false;

        try {
            // 开始事务
            $openedTransaction = $this->beginTransactionIfNotInOne();

            // 创建商品
            $sourceUrl = $request->source_url;
            // 确保 source_url 没有查询参数（虽然验证时已经处理过，但这里再处理一次确保安全）
            if ($sourceUrl !== null && $sourceUrl !== '') {
                $questionMarkPos = strpos($sourceUrl, '?');
                if ($questionMarkPos !== false) {
                    $sourceUrl = substr($sourceUrl, 0, $questionMarkPos);
                }
            }

            $productData = [
                'slug' => $request->slug,
                'source_url' => $sourceUrl,
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
