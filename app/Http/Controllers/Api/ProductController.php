<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HandlesApiTransactions;
use App\Http\Controllers\Api\Concerns\HandlesApiResponses;
use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use HandlesApiTransactions, HandlesApiResponses;

    public function __construct(
        protected ProductService $productService
    ) {
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $openedTransaction = false;
        
        try {
            // 开始事务
            $openedTransaction = $this->beginTransactionIfNotInOne();

            // 创建商品
            $product = $this->productService->createProduct([
                'slug' => $request->slug,
                'main_image' => $request->main_image,
                'content_images' => $request->content_images,
                'translations' => $request->translations,
                'categories' => $request->categories,
                'variants' => $request->variants,
            ]);

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
