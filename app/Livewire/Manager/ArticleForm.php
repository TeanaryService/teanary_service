<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesMediaUploads;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Traits\HandlesEditorUploads;
use Livewire\Component;

class ArticleForm extends Component
{
    use HandlesEditorUploads;
    use HandlesMediaUploads;
    use HandlesTranslations;
    use HasNavigationRedirect;
    use UsesLocaleCurrency;

    public ?int $articleId = null;
    public string $slug = '';
    public ?int $userId = null;
    public bool $isPublished = false;

    protected array $rules = [
        'slug' => 'required|max:255|unique:articles,slug',
        'userId' => 'nullable|exists:users,id',
        'isPublished' => 'boolean',
        'translationStatus' => 'required',
        'image' => 'nullable|image|max:5120',
        'translations.*.title' => 'required|max:255',
        'translations.*.summary' => 'nullable|max:500',
        'translations.*.content' => 'nullable',
    ];

    protected array $messages = [
        'slug.required' => 'URL别名不能为空',
        'slug.max' => 'URL别名不能超过255个字符',
        'slug.unique' => '该URL别名已存在',
        'userId.exists' => '选择的用户不存在',
        'translationStatus.required' => '翻译状态不能为空',
        'image.image' => '上传的文件必须是图片',
        'image.max' => '图片大小不能超过5MB',
        'translations.*.title.required' => '文章标题不能为空',
        'translations.*.title.max' => '文章标题不能超过255个字符',
        'translations.*.summary.max' => '摘要不能超过500个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->initializeTranslationStatus();

        if ($id) {
            $this->articleId = $id;
            $article = Article::with('articleTranslations')->findOrFail($id);
            $this->slug = $article->slug;
            $this->userId = $article->user_id;
            $this->isPublished = $article->is_published;
            $this->translationStatus = $article->translation_status->value;

            // 获取图片
            $this->loadImageUrl($article, 'image', 'thumb');

            // 加载翻译（需要特殊处理 content 字段）
            $this->initializeTranslations($article, 'articleTranslations', ['title', 'summary', 'content']);
        } else {
            // 初始化翻译数组
            $this->initializeTranslations(null, 'articleTranslations', ['title', 'summary', 'content']);
        }
    }

    public function save()
    {
        // 验证默认语言必须填写
        if (! $this->validateDefaultLanguage('title', '默认语言的文章标题不能为空')) {
            return;
        }

        // 根据是否是编辑模式动态设置验证规则
        if ($this->articleId) {
            // 编辑模式：slug 需要忽略当前文章
            $this->rules['slug'] = 'required|max:255|unique:articles,slug,'.$this->articleId;
        } else {
            // 创建模式：slug 必须唯一
            $this->rules['slug'] = 'required|max:255|unique:articles,slug';
        }

        $this->validate();

        $data = [
            'slug' => $this->slug,
            'user_id' => $this->userId,
            'is_published' => $this->isPublished,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->articleId) {
            $article = Article::findOrFail($this->articleId);
            $article->update($data);

            // 处理图片上传
            $this->saveImage($article, 'image', true);

            // 更新翻译
            $oldContents = [];
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['title'])) {
                    $existing = ArticleTranslation::where('article_id', $article->id)
                        ->where('language_id', $languageId)
                        ->first();

                    $oldContents[$languageId] = $existing ? $existing->content : null;

                    // 清理 HTML 内容
                    $content = $this->cleanEditorHtml($translation['content'] ?? '');

                    ArticleTranslation::updateOrCreate(
                        [
                            'article_id' => $article->id,
                            'language_id' => $languageId,
                        ],
                        [
                            'title' => $translation['title'],
                            'summary' => $translation['summary'] ?? null,
                            'content' => $content,
                        ]
                    );
                }
            }

            // 处理编辑器上传同步
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['content'])) {
                    $oldContent = $oldContents[$languageId] ?? null;
                    $newContent = $translation['content'];
                    $this->syncEditorUploadsFromHtml($oldContent, $newContent);
                }
            }

            $this->flashMessage('updated_successfully');
        } else {
            $article = Article::create($data);

            // 处理图片上传
            $this->saveImage($article, 'image', false);

            // 创建翻译
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['title'])) {
                    // 清理 HTML 内容
                    $content = $this->cleanEditorHtml($translation['content'] ?? '');

                    ArticleTranslation::create([
                        'article_id' => $article->id,
                        'language_id' => $languageId,
                        'title' => $translation['title'],
                        'summary' => $translation['summary'] ?? null,
                        'content' => $content,
                    ]);
                }
            }

            // 处理编辑器上传
            foreach ($this->translations as $translation) {
                if (! empty($translation['content'])) {
                    $this->handleEditorUploadsFromHtml($translation['content']);
                }
            }

            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.articles', $this->articleId ? 'updated_successfully' : 'created_successfully');
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->imageUrl = null;

        if (! $this->articleId) {
            return;
        }

        $article = Article::findOrFail($this->articleId);
        $article->clearMediaCollection('image');
        $this->imageUrl = null;
    }

    public function render()
    {
        $users = \App\Models\User::orderBy('name')->get();

        return view('livewire.manager.article-form', [
            'languages' => $this->getLanguages(),
            'users' => $users,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
