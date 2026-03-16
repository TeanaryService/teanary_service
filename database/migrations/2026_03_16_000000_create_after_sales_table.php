<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('after_sales', function (Blueprint $table) {
            // 与现有 Snowflake 风格保持一致，使用无符号 bigInteger 作为主键
            $table->bigInteger('id')->unsigned()->primary();

            // 关联主体
            $table->bigInteger('order_id')->unsigned();
            $table->bigInteger('order_item_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();

            // 业务字段
            $table->string('type', 32)->comment('refund_only, refund_and_return, exchange');
            $table->string('status', 32)->default('pending');
            $table->string('reason')->nullable();
            $table->text('description')->nullable();

            $table->integer('quantity')->default(1);
            $table->decimal('refund_amount', 12, 2)->nullable();

            $table->bigInteger('exchange_product_id')->unsigned()->nullable()->comment('换货时的目标商品ID');

            // 用户上传的凭证图片，可以与现有 media/上传体系结合，这里先用 JSON 预留
            $table->json('images')->nullable();

            // 管理端备注
            $table->text('remarks')->nullable();

            // 退货物流信息
            $table->string('logistics_company')->nullable();
            $table->string('tracking_number')->nullable();

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // 索引
            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('type');

            // 外键（为保持灵活性，暂不强制约束，可视需要打开）
            // $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            // $table->foreign('order_item_id')->references('id')->on('order_items')->nullOnDelete();
            // $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            // $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            // $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('after_sales');
    }
};
