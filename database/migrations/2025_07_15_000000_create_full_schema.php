<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ProductStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PromotionTypeEnum;
use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;

return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------
        // Categories
        // -----------------------------
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // -----------------------------
        // Products
        // -----------------------------
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('status', ProductStatusEnum::values())->default(ProductStatusEnum::default()->value);
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        });

        // -----------------------------
        // Attributes
        // -----------------------------
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('attribute_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // -----------------------------
        // Specifications
        // -----------------------------
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('specification_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('specification_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specification_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('specification_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specification_value_id', 'svt_spec_value_fk')->constrained('specification_values')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // -----------------------------
        // Product Variants
        // -----------------------------
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('weight', 12, 2)->nullable();
            $table->decimal('length', 12, 2)->nullable();
            $table->decimal('width', 12, 2)->nullable();
            $table->decimal('height', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('product_variant_specification_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('specification_value_id');
            $table->foreign('specification_value_id', 'pvsv_spec_value_fk')
                ->references('id')
                ->on('specification_values')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::create('product_attribute_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_value_id'], 'product_attribute_value_unique');
        });

        // -----------------------------
        // Carts
        // -----------------------------
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('qty')->default(1);
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });

        // -----------------------------
        // Shipping Methods
        // -----------------------------
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->boolean('active')->default(true);
            $table->string('api_url')->nullable();
            $table->string('api_token')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_method_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // -----------------------------
        // Payment Methods
        // -----------------------------
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->boolean('active')->default(true);
            $table->string('api_url')->nullable();
            $table->string('api_token')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_method_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // -----------------------------
        // Orders
        // -----------------------------
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_no')->unique();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', OrderStatusEnum::values())->default(OrderStatusEnum::default()->value);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('qty');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });

        // -----------------------------
        // Promotions
        // -----------------------------
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', PromotionTypeEnum::values());
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('promotion_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('promotion_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->enum('condition_type', PromotionConditionTypeEnum::values());
            $table->decimal('condition_value', 12, 2);
            $table->enum('discount_type', PromotionDiscountTypeEnum::values());
            $table->decimal('discount_value', 12, 2);
            $table->timestamps();
        });

        Schema::create('promotion_user_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('promotion_product_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['promotion_id', 'product_variant_id'], 'promotion_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_product_variant');
        Schema::dropIfExists('promotion_user_group');
        Schema::dropIfExists('promotion_rules');
        Schema::dropIfExists('promotion_translations');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('payment_method_translations');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('shipping_method_translations');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_variant_specification_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('specification_value_translations');
        Schema::dropIfExists('specification_values');
        Schema::dropIfExists('specification_translations');
        Schema::dropIfExists('specifications');
        Schema::dropIfExists('attribute_value_translations');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attribute_translations');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_attribute_value');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
    }
};
