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
        Schema::table('sync_logs', function (Blueprint $table) {
            // 将 payload 字段从 text 改为 longText，以支持更大的数据
            $table->longText('payload')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            // 回滚时将 payload 字段改回 text
            $table->text('payload')->nullable()->change();
        });
    }
};
