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
        Schema::create('traffic_statistics', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string('path', 500)->index();
            $table->string('method', 10)->default('GET')->index();
            $table->string('ip', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('locale', 10)->nullable()->index();
            $table->unsignedInteger('count')->default(1)->comment('同一分钟内相同路径的访问次数');
            $table->dateTime('stat_date')->index()->comment('统计日期（精确到分钟）');
            $table->boolean('is_bot')->default(false)->index()->comment('是否为爬虫');
            $table->string('spider_source', 50)->nullable()->index()->comment('爬虫来源（如google、bing等）');
            $table->timestamps();

            // 复合索引，用于快速查询和去重（包含is_bot和spider_source）
            $table->index(['stat_date', 'path', 'method', 'ip', 'is_bot', 'spider_source'], 'traffic_statistics_composite_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traffic_statistics');
    }
};
