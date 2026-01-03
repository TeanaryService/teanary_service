<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MediaService
{
    /**
     * 解码 base64 图片数据
     *
     * @param string $base64Data base64编码的图片数据
     * @return string 解码后的二进制数据
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
     * 处理主图上传
     *
     * @param Model $model 支持 HasMedia 的模型
     * @param array|null $mainImage 主图数据
     * @param string $extension 文件扩展名，默认 'png'
     * @return void
     * @throws InvalidArgumentException
     */
    public function handleMainImage(Model $model, ?array $mainImage, string $extension = 'png'): void
    {
        if (!$mainImage || !isset($mainImage['contents'])) {
            return;
        }

        $imageContent = $this->decodeBase64Image($mainImage['contents']);
        $imageId = $mainImage['image_id'] ?? Str::random(8);
        
        $model->addMediaFromString($imageContent)
            ->usingFileName($imageId.'.'.$extension)
            ->toMediaCollection('image');
    }

    /**
     * 处理内容图片上传并返回图片映射
     *
     * @param Model $model 支持 HasMedia 的模型
     * @param array|null $contentImages 内容图片数组
     * @param string $extension 文件扩展名，默认 'png'
     * @return array 图片ID到URL的映射
     * @throws InvalidArgumentException
     */
    public function handleContentImages(Model $model, ?array $contentImages, string $extension = 'png'): array
    {
        $imageMap = [];

        if (!$contentImages || !is_array($contentImages)) {
            return $imageMap;
        }

        foreach ($contentImages as $image) {
            if (!isset($image['contents']) || !isset($image['image_id'])) {
                continue;
            }

            $imageContent = $this->decodeBase64Image($image['contents']);
            
            $mediaItem = $model->addMediaFromString($imageContent)
                ->usingFileName($image['image_id'].'.'.$extension)
                ->toMediaCollection('content-images');

            $imageMap[$image['image_id']] = $mediaItem->getUrl();
        }

        return $imageMap;
    }

    /**
     * 替换内容中的图片占位符
     *
     * @param string|null $content 内容
     * @param array $imageMap 图片ID到URL的映射
     * @return string 替换后的内容
     */
    public function replaceImagePlaceholders(?string $content, array $imageMap): string
    {
        if (!$content || empty($imageMap)) {
            return $content ?? '';
        }

        foreach ($imageMap as $imageId => $url) {
            $url = '/storage'.Str::of($url)->after('/storage');
            $content = str_replace(
                '{{image:'.$imageId.'}}',
                $url,
                $content
            );
        }

        return $content;
    }
}

