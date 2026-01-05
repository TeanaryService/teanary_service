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
        Schema::table('products', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('status');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('translation_status')->default('not_translated')->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('translation_status');
        });
    }
};
