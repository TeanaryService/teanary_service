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
        // 属性
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('is_filterable');
        });

        // 属性值
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('attribute_id');
        });

        // 规格
        Schema::table('specifications', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated');
        });

        // 规格值
        Schema::table('specification_values', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('specification_id');
        });

        // 分类
        Schema::table('categories', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('slug');
        });

        // 国家
        Schema::table('countries', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('active');
        });

        // 地区
        Schema::table('zones', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('active');
        });

        // 促销
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('active');
        });

        // 用户组
        Schema::table('user_groups', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('specifications', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('specification_values', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('user_groups', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });
    }
};
