<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\EditorUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EditorUploadController extends Controller
{
    public function storeImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB
        ]);

        $file = $request->file('image');
        if (! $file) {
            return response()->json(['message' => '未找到上传文件'], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $dir = 'editor-uploads/'.now()->format('Y/m');
        $name = (string) Str::uuid().'.'.$ext;

        $path = $file->storeAs($dir, $name, 'public'); // relative path on public disk

        EditorUpload::create([
            'path' => $path,
            'used' => false,
        ]);

        // 返回相对路径，避免把域名写入富文本内容
        $relativeUrl = '/storage/'.ltrim($path, '/');

        return response()->json([
            'url' => $relativeUrl,
            'path' => $path,
        ]);
    }
}
