<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $article = Article::create([
            'slug' => $request->slug,
            'is_published' => $request->is_published ?? false,
            'user_id' => auth()->id(),
        ]);

        foreach ($request->translations as $translation) {
            $article->articleTranslations()->create([
                'language_id' => $translation['language_id'],
                'title' => $translation['title'],
                'summary' => $translation['summary'] ?? null,
                'content' => $translation['content'] ?? null,
            ]);
        }

        return response()->json([
            'message' => '文章创建成功',
            'data' => $article->load('articleTranslations')
        ], 201);
    }
}
