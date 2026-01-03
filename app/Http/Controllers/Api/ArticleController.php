<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HandlesApiTransactions;
use App\Http\Controllers\Api\Concerns\HandlesApiResponses;
use App\Http\Requests\StoreArticleRequest;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    use HandlesApiTransactions, HandlesApiResponses;

    public function __construct(
        protected ArticleService $articleService
    ) {
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $openedTransaction = false;
        
        try {
            // 检查中文标题是否重复
            $translations = collect($request->translations);
            $existingTranslation = $this->articleService->checkDuplicateChineseTitle($translations);
            
            if ($existingTranslation) {
                return $this->successResponse('中文标题已存在', null, 200);
            }

            // 开始事务
            $openedTransaction = $this->beginTransactionIfNotInOne();

            // 创建文章
            $article = $this->articleService->createArticle([
                'slug' => $request->slug,
                'is_published' => true,
                'main_image' => $request->main_image,
                'content_images' => $request->content_images,
                'translations' => $request->translations,
            ]);

            // 提交事务
            $this->commitIfOpened($openedTransaction);

            return $this->successResponse('文章创建成功', $article);
        } catch (\Exception $e) {
            $this->rollbackIfOpened($openedTransaction);
            
            return $this->handleException($e, '文章创建失败', [
                'request' => $request->all(),
            ]);
        }
    }
}
