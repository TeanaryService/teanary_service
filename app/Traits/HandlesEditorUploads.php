<?php

namespace App\Traits;

use App\Models\EditorUpload;
use Illuminate\Support\Facades\Storage;

trait HandlesEditorUploads
{
    /**
     * 清理富文本内容：只保留指定标签，并去掉标签属性.
     */
    public function cleanEditorHtml(string $html): string
    {
        // 先将 HTML 实体转成普通字符（去掉 &nbsp; 等）
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 允许的标签
        $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><li><img>';

        // 去掉 script/style
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $html = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $html);

        // 只保留允许标签
        $html = strip_tags($html, $allowedTags);

        // 保留 <img> 但只留 src
        $html = preg_replace_callback('/<img[^>]*>/i', function ($matches) {
            if (preg_match('/src\s*=\s*([\'"])(.*?)\1/i', $matches[0], $srcMatch)) {
                return '<img src="'.htmlspecialchars($srcMatch[2], ENT_QUOTES).'">';
            }

            return '<img>';
        }, $html);

        // 其他标签去掉所有属性
        $html = preg_replace('/<(?!img)(\w+)[^>]*>/i', '<$1>', $html);

        // 去掉多余空格
        $html = preg_replace('/\s+/', ' ', $html);

        // 去掉空标签
        $html = preg_replace('/<(\w+)>\s*<\/\1>/', '', $html);

        return trim($html);
    }

    /**
     * 提取富文本中的 <img src="..."> 路径，并转换为相对路径.
     */
    public function extractImagePathsFromHtml(string $html): array
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $matches);
        $srcs = $matches[1] ?? [];

        return collect($srcs)
            ->map(function ($url) {
                return str_replace(Storage::disk('public')->url(''), '', $url);
            })
            ->toArray();
    }

    /**
     * 将上传记录标记为已使用；如果记录不存在，则创建它。
     */
    public function markEditorUploadsAsUsed(array $paths): void
    {
        if (empty($paths)) {
            return;
        }

        foreach ($paths as $path) {
            EditorUpload::updateOrCreate(
                ['path' => $path],
                ['used' => true]
            );
        }
    }

    /**
     * 从富文本中提取图片并标记为已使用（新增过滤）.
     */
    public function handleEditorUploadsFromHtml(?string $html): void
    {
        if (blank($html)) {
            return;
        }

        // 先清理 HTML
        $html = $this->cleanEditorHtml($html);

        // 提取图片路径
        $paths = $this->extractImagePathsFromHtml($html);
        $this->markEditorUploadsAsUsed($paths);
    }

    public function deleteEditorUploadsByPaths(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
            EditorUpload::where('path', $path)->delete();
        }
    }

    public function deleteEditorUploadsFromHtml(?string $html): void
    {
        if (blank($html)) {
            return;
        }

        $html = $this->cleanEditorHtml($html);
        $paths = $this->extractImagePathsFromHtml($html);
        $this->deleteEditorUploadsByPaths($paths);
    }

    public function deleteEditorUploadsFromMultipleFields(array $htmlFields): void
    {
        foreach ($htmlFields as $html) {
            $this->deleteEditorUploadsFromHtml($html);
        }
    }

    public function syncEditorUploadsFromHtml(?string $oldHtml, ?string $newHtml): void
    {
        $oldHtml = $this->cleanEditorHtml($oldHtml ?? '');
        $newHtml = $this->cleanEditorHtml($newHtml ?? '');

        $oldPaths = collect($this->extractImagePathsFromHtml($oldHtml));
        $newPaths = collect($this->extractImagePathsFromHtml($newHtml));

        $deletedPaths = $oldPaths->diff($newPaths)->values()->all();
        $addedPaths = $newPaths->diff($oldPaths)->values()->all();

        $this->deleteEditorUploadsByPaths($deletedPaths);
        $this->markEditorUploadsAsUsed($addedPaths);
    }
}
