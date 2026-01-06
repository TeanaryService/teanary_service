<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // 模型类名
            $table->unsignedBigInteger('model_id'); // 模型ID
            $table->string('action'); // created, updated, deleted
            $table->string('source_node'); // 来源节点
            $table->string('target_node'); // 目标节点
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('payload')->nullable(); // JSON格式的数据
            $table->text('error_message')->nullable(); // 错误信息
            $table->integer('retry_count')->default(0); // 重试次数
            $table->timestamp('synced_at')->nullable(); // 同步完成时间
            $table->timestamps();

            // 索引
            $table->index(['model_type', 'model_id']);
            $table->index(['status', 'created_at']);
            $table->index(['source_node', 'target_node']);
        });

        // 同步状态表，用于记录每个模型的最后同步时间，避免重复同步
        Schema::create('sync_status', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // 模型类名
            $table->unsignedBigInteger('model_id'); // 模型ID
            $table->string('node'); // 节点标识
            $table->timestamp('last_synced_at')->nullable(); // 最后同步时间
            $table->string('sync_hash')->nullable(); // 数据哈希值，用于检测变更
            $table->timestamps();

            // 唯一索引，确保每个节点每个模型只有一条记录
            $table->unique(['model_type', 'model_id', 'node'], 'sync_status_unique');
            $table->index(['model_type', 'node', 'last_synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_status');
        Schema::dropIfExists('sync_logs');
    }
};
