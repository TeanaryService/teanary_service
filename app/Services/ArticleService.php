<?php

namespace App\Services;

use App\Enums\TranslationStatusEnum;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Language;
use Illuminate\Support\Collection;

class ArticleService
{
    private const CHINESE_LANGUAGE_CODE = 'zh_CN';

    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * 检查中文标题是否重复.
     *
     * @return ArticleTranslation|null 如果重复返回已存在的翻译，否则返回null
     */
    public function checkDuplicateChineseTitle(Collection $translations): ?ArticleTranslation
    {
        $chineseLanguage = Language::where('code', self::CHINESE_LANGUAGE_CODE)->first();
        if (! $chineseLanguage) {
            return null;
        }

        $chineseTranslation = $translations->firstWhere('language_id', $chineseLanguage->id);
        if (! $chineseTranslation || ! isset($chineseTranslation['title'])) {
            return null;
        }

        return ArticleTranslation::where('language_id', $chineseLanguage->id)
            ->where('title', $chineseTranslation['title'])
            ->first();
    }

    /**
     * 创建文章.
     */
    public function createArticle(array $data): Article
    {
        $article = Article::create([
            'slug' => $data['slug'],
            'is_published' => $data['is_published'] ?? true,
            'translation_status' => TranslationStatusEnum::NotTranslated, // 默认不翻译
        ]);

        // 处理主图
        if (isset($data['main_image'])) {
            $this->mediaService->handleMainImage($article, $data['main_image'], 'jpg');
        }

        // 处理内容图片
        $imageMap = $this->mediaService->handleContentImages($article, $data['content_images'] ?? null, 'jpg');

        // 处理翻译内容
        $this->createArticleTranslations($article, $data['translations'], $imageMap);

        // 触发搜索索引
        $article->searchable();

        return $article->load(['articleTranslations', 'media']);
    }

    /**
     * 创建文章翻译.
     */
    protected function createArticleTranslations(Article $article, array $translations, array $imageMap): void
    {
        foreach ($translations as $translation) {
            $content = $this->mediaService->replaceImagePlaceholders(
                $translation['content'] ?? null,
                $imageMap
            );

            $article->articleTranslations()->create([
                'language_id' => $translation['language_id'],
                'title' => $translation['title'],
                'content' => $content,
                'summary' => $translation['summary'] ?? null,
            ]);
        }
    }
}
