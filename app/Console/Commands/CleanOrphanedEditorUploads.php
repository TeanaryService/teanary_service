<?php

use App\Models\EditorUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOrphanedEditorUploads extends Command
{
    protected $signature = 'app:clean-orphans';
    protected $description = '清理孤岛富文本上传文件';

    public function handle()
    {
        $expired = EditorUpload::where('used', false)
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        foreach ($expired as $upload) {
            Storage::disk('public')->delete($upload->path);
            $upload->delete();
        }

        $this->info("共清理 {$expired->count()} 个孤岛文件");
    }
}
