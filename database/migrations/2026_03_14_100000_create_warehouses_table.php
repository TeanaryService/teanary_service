<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string('name');
            $table->string('code', 32)->unique()->comment('仓库编码');
            $table->string('telephone', 50)->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode', 20)->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false)->comment('是否默认仓库');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_warehouse', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('warehouse_id')->unsigned();
            $table->primary(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouse');
        Schema::dropIfExists('warehouses');
    }
};
