<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Services\LocaleCurrencyService;
use App\Traits\HandlesEditorUploads;
use Livewire\Component;
use Livewire\WithFileUploads;

class ArticleForm extends Component
{
    use WithFileUploads;
    use HandlesEditorUploads;

    public ?int $articleId = null;
    public string $slug = '';
    public ?int $userId = null;
    public bool $isPublished = false;
    public string $translationStatus = '';
    public $image;
    public ?string $imageUrl = null;
    public array $translations = [];

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
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        if ($id) {
            $this->articleId = $id;
            $article = Article::with('articleTranslations')->findOrFail($id);
            $this->slug = $article->slug;
            $this->userId = $article->user_id;
            $this->isPublished = $article->is_published;
            $this->translationStatus = $article->translation_status->value;
            
            // 获取图片
            if ($article->hasMedia('image')) {
                $this->imageUrl = $article->getFirstMediaUrl('image', 'thumb');
            }

            // 加载翻译
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $translation = $article->articleTranslations->where('language_id', $language->id)->first();
                $this->translations[$language->id] = [
                    'title' => $translation ? $translation->title : '',
                    'summary' => $translation ? ($translation->summary ?? '') : '',
                    'content' => $translation ? ($translation->content ?? '') : '',
                ];
            }

            // 更新验证规则，忽略当前记录
            $this->rules['slug'] = 'required|max:255|unique:articles,slug,' . $id;
        } else {
            // 初始化翻译数组
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $this->translations[$language->id] = [
                    'title' => '',
                    'summary' => '',
                    'content' => '',
                ];
            }
        }
    }

    public function save()
    {
        // 验证默认语言必须填写
        $service = app(LocaleCurrencyService::class);
        $defaultLanguage = $service->getLanguages()->firstWhere('default', true);
        if ($defaultLanguage && empty($this->translations[$defaultLanguage->id]['title'])) {
            $this->addError('translations.' . $defaultLanguage->id . '.title', '默认语言的文章标题不能为空');
            return;
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
            if ($this->image) {
                $article->clearMediaCollection('image');
                $article->addMedia($this->image->getRealPath())
                    ->toMediaCollection('image');
                $this->image = null; // 清除临时文件
            }

            // 更新翻译
            $oldContents = [];
            foreach ($this->translations as $languageId => $translation) {
                if (!empty($translation['title'])) {
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
                if (!empty($translation['content'])) {
                    $oldContent = $oldContents[$languageId] ?? null;
                    $newContent = $translation['content'];
                    $this->syncEditorUploadsFromHtml($oldContent, $newContent);
                }
            }

            session()->flash('message', __('app.updated_successfully'));
        } else {
            $article = Article::create($data);

            // 处理图片上传
            if ($this->image) {
                $article->addMedia($this->image->getRealPath())
                    ->toMediaCollection('image');
                $this->image = null; // 清除临时文件
            }

            // 创建翻译
            foreach ($this->translations as $languageId => $translation) {
                if (!empty($translation['title'])) {
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
                if (!empty($translation['content'])) {
                    $this->handleEditorUploadsFromHtml($translation['content']);
                }
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.articles'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $users = \App\Models\User::orderBy('name')->get();

        return view('livewire.manager.article-form', [
            'languages' => $languages,
            'users' => $users,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
