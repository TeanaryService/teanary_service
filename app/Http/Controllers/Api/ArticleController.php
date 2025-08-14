<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function store(StoreArticleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. 创建文章基础信息
            $article = Article::create([
                'slug' => $request->slug,
                'is_published' => true,
                // 'article_id' => $request->article_id,
            ]);

            Log::info(json_encode($request->main_image));

            // 2. 处理主图
            if ($request->has('main_image.contents')) {
                $imageContent = base64_decode($request->main_image['contents']);
                $article->addMediaFromString($imageContent)
                    ->usingFileName($request->main_image['image_id'] . '.jpg')
                    ->toMediaCollection('image');
            }

            // 3. 处理内容图片
            $imageMap = [];
            if ($request->has('content_images')) {
                foreach ($request->content_images as $image) {
                    $mediaItem = $article->addMediaFromString(base64_decode($image['contents']))
                        ->usingFileName($image['image_id'] . '.jpg')
                        ->toMediaCollection('content-images');

                    $imageMap[$image['image_id']] = $mediaItem->getUrl();
                }
            }

            // 4. 处理翻译内容
            foreach ($request->translations as $translation) {
                $content = $translation['content'];

                // 替换内容中的图片占位符
                foreach ($imageMap as $imageId => $url) {
                    $url = "/storage" . Str::of($url)->after('/storage');
                    $content = str_replace(
                        "{{image:" . $imageId . "}}",
                        $url,
                        $content
                    );
                }

                $article->articleTranslations()->create([
                    'language_id' => $translation['language_id'],
                    'title' => $translation['title'],
                    'content' => $content,
                    'summary' => $translation['summary'] ?? null,
                ]);
            }

            DB::commit();

            // 手动触发索引
            $article->searchable();

            return response()->json([
                'message' => '文章创建成功',
                'data' => $article->load(['articleTranslations', 'media'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('文章创建失败：' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => '文章创建失败',
                'error' => config('app.debug') ? $e->getMessage() : '系统错误'
            ], 500);
        }
    }
}
