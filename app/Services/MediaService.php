<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MediaService
{
    /**
     * 解码 base64 图片数据.
     *
     * @param  string  $base64Data  base64编码的图片数据
     * @return string 解码后的二进制数据
     *
     * @throws InvalidArgumentException
     */
    protected function decodeBase64Image(string $base64Data): string
    {
        $imageContent = base64_decode($base64Data, true);
        if ($imageContent === false) {
            throw new InvalidArgumentException('无效的 base64 图片数据');
        }

        return $imageContent;
    }

    /**
     * 从URL下载图片.
     *
     * @param  string  $url  图片URL
     * @return string 图片二进制数据
     *
     * @throws Exception
     */
    protected function downloadImageFromUrl(string $url): string
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(30)->get($url);

            if ($response->status() !== 200) {
                throw new Exception("无法下载图片: HTTP {$response->status()}");
            }

            $imageContent = $response->body();

            if (empty($imageContent)) {
                throw new Exception('下载的图片内容为空');
            }

            // 验证是否为有效的图片数据
            $imageInfo = @getimagesizefromstring($imageContent);
            if ($imageInfo === false) {
                throw new Exception('下载的内容不是有效的图片');
            }

            return $imageContent;
        } catch (Exception $e) {
            throw new Exception("下载图片失败: {$e->getMessage()}");
        }
    }

    /**
     * 从URL获取图片扩展名.
     *
     * @param  string  $url  图片URL
     * @return string 扩展名
     */
    protected function getImageExtensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // 常见的图片扩展名
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($extension);

        if (in_array($extension, $validExtensions)) {
            return $extension;
        }

        // 默认返回jpg
        return 'jpg';
    }

    /**
     * 处理主图上传（单个或数组）.
     *
     * @param  Model  $model  支持 HasMedia 的模型
     * @param  array|null  $mainImage  主图数据，支持单个主图对象或主图数组
     * @param  string  $extension  文件扩展名，默认 'png'，如果提供image_url会自动检测
     *
     * @throws InvalidArgumentException|Exception
     */
    public function handleMainImage(Model $model, ?array $mainImage, string $extension = 'png'): void
    {
        if (! $mainImage) {
            return;
        }

        // 检查是否是数组格式（多个主图）
        // 如果第一个元素是数组，或者数组有数字索引且第一个元素包含 image_id 或 image_url，说明是多个主图的数组
        if (isset($mainImage[0]) && is_array($mainImage[0]) && (isset($mainImage[0]['image_id']) || isset($mainImage[0]['image_url']) || isset($mainImage[0]['contents']))) {
            // 处理多个主图
            foreach ($mainImage as $image) {
                $this->handleSingleMainImage($model, $image, $extension);
            }
        } else {
            // 处理单个主图（兼容旧格式）
            $this->handleSingleMainImage($model, $mainImage, $extension);
        }
    }

    /**
     * 处理单个主图上传.
     *
     * @param  Model  $model  支持 HasMedia 的模型
     * @param  array  $mainImage  主图数据，支持 contents (base64) 或 image_url (URL)
     * @param  string  $extension  文件扩展名，默认 'png'，如果提供image_url会自动检测
     *
     * @throws InvalidArgumentException|Exception
     */
    protected function handleSingleMainImage(Model $model, array $mainImage, string $extension = 'png'): void
    {
        $imageId = $mainImage['image_id'] ?? Str::random(8);
        $imageContent = null;
        $finalExtension = $extension;

        // 优先使用 image_url，如果没有则使用 contents
        if (isset($mainImage['image_url'])) {
            $imageContent = $this->downloadImageFromUrl($mainImage['image_url']);
            $finalExtension = $this->getImageExtensionFromUrl($mainImage['image_url']);
        } elseif (isset($mainImage['contents'])) {
            $imageContent = $this->decodeBase64Image($mainImage['contents']);
        } else {
            return;
        }

        $model->addMediaFromString($imageContent)
            ->usingFileName($imageId.'.'.$finalExtension)
            ->toMediaCollection('images');
    }

    /**
     * 处理内容图片上传并返回图片映射.
     *
     * @param  Model  $model  支持 HasMedia 的模型
     * @param  array|null  $contentImages  内容图片数组，支持 contents (base64) 或 image_url (URL)
     * @param  string  $extension  文件扩展名，默认 'png'，如果提供image_url会自动检测
     * @return array 图片ID到URL的映射
     *
     * @throws InvalidArgumentException|Exception
     */
    public function handleContentImages(Model $model, ?array $contentImages, string $extension = 'png'): array
    {
        $imageMap = [];

        if (! $contentImages || ! is_array($contentImages)) {
            return $imageMap;
        }

        foreach ($contentImages as $image) {
            if (! isset($image['image_id'])) {
                continue;
            }

            $imageContent = null;
            $finalExtension = $extension;

            // 优先使用 image_url，如果没有则使用 contents
            if (isset($image['image_url'])) {
                $imageContent = $this->downloadImageFromUrl($image['image_url']);
                $finalExtension = $this->getImageExtensionFromUrl($image['image_url']);
            } elseif (isset($image['contents'])) {
                $imageContent = $this->decodeBase64Image($image['contents']);
            } else {
                continue;
            }

            $mediaItem = $model->addMediaFromString($imageContent)
                ->usingFileName($image['image_id'].'.'.$finalExtension)
                ->toMediaCollection('content-images');

            $imageMap[$image['image_id']] = $mediaItem->getUrl();
        }

        return $imageMap;
    }

    /**
     * 替换内容中的图片占位符为HTML img标签.
     *
     * @param  string|null  $content  内容（HTML格式）
     * @param  array  $imageMap  图片ID到URL的映射
     * @return string 替换后的HTML内容
     */
    public function replaceImagePlaceholders(?string $content, array $imageMap): string
    {
        if (! $content) {
            return '';
        }

        if (empty($imageMap)) {
            return $content;
        }

        foreach ($imageMap as $imageId => $url) {
            // 确保URL是相对路径
            $url = '/storage'.Str::of($url)->after('/storage');

            // 替换占位符为HTML img标签
            $imgTag = '<img src="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" alt="" class="product-content-image" />';

            // 替换各种可能的占位符格式
            $placeholders = [
                '{{image:'.$imageId.'}}',
                '{{ image:'.$imageId.' }}',
                '{{image: '.$imageId.'}}',
                '{{ image: '.$imageId.' }}',
            ];

            foreach ($placeholders as $placeholder) {
                $content = str_replace($placeholder, $imgTag, $content);
            }
        }

        return $content;
    }
}
