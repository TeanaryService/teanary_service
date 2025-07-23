<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use App\Models\EditorUpload;

trait HandlesEditorUploads
{
    /**
     * 提取富文本中的 <img src="..."> 路径，并转换为相对路径
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
        if (empty($paths)) return;

        foreach ($paths as $path) {
            EditorUpload::updateOrCreate(
                ['path' => $path],
                ['used' => true]
            );
        }
    }

    /**
     * 从富文本中提取图片并标记为已使用
     */
    public function handleEditorUploadsFromHtml(?string $html): void
    {
        if (blank($html)) return;

        $paths = $this->extractImagePathsFromHtml($html);
        $this->markEditorUploadsAsUsed($paths);
    }

    /**
     * 删除指定路径的文件及数据库记录
     */
    public function deleteEditorUploadsByPaths(array $paths): void
    {
        foreach ($paths as $path) {
            // 删除物理文件
            Storage::disk('public')->delete($path);

            // 删除数据库记录
            EditorUpload::where('path', $path)->delete();
        }
    }

    /**
     * 从富文本中提取图片并删除（用于文章/商品删除时）
     */
    public function deleteEditorUploadsFromHtml(?string $html): void
    {
        if (blank($html)) return;

        $paths = $this->extractImagePathsFromHtml($html);
        $this->deleteEditorUploadsByPaths($paths);
    }

    /**
     * 批量处理多个 HTML 字段（多语言场景）
     */
    public function deleteEditorUploadsFromMultipleFields(array $htmlFields): void
    {
        foreach ($htmlFields as $html) {
            $this->deleteEditorUploadsFromHtml($html);
        }
    }

    public function syncEditorUploadsFromHtml(?string $oldHtml, ?string $newHtml): void
    {
        $oldPaths = collect($this->extractImagePathsFromHtml($oldHtml ?? ''));
        $newPaths = collect($this->extractImagePathsFromHtml($newHtml ?? ''));

        // 被删除的旧图（存在于旧内容中，但不再出现在新内容中）
        $deletedPaths = $oldPaths->diff($newPaths)->values()->all();

        // 新增的图（存在于新内容中，但旧内容中没有）
        $addedPaths = $newPaths->diff($oldPaths)->values()->all();

        // 删除旧图
        $this->deleteEditorUploadsByPaths($deletedPaths);

        // 标记新图为已使用
        $this->markEditorUploadsAsUsed($addedPaths);
    }
}
