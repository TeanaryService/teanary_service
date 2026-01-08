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
        Schema::create('articles', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string('slug')->unique(); // 用于 SEO 路径
            $table->boolean('is_published')->default(false);
            $table->string('translation_status')->default('not_translated');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('article_translations', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->bigInteger('article_id')->unsigned();
            $table->string('language_id');

            $table->string('title');
            $table->text('summary')->nullable(); // 简短摘要
            $table->text('content')->nullable(); // 富文本内容

            $table->unique(['article_id', 'language_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_translations');
        Schema::dropIfExists('articles');
    }
};
