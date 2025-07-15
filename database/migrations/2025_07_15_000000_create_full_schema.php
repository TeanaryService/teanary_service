<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ProductStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PromotionTypeEnum;
use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use App\Enums\AttributeTypeEnum;

return new class extends Migration
{
    public function up(): void
    {
        // // -----------------------------
        // // Languages
        // // -----------------------------
        // Schema::create('languages', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code', 10)->unique();
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // // -----------------------------
        // // Currencies
        // // -----------------------------
        // Schema::create('currencies', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code', 10)->unique();
        //     $table->string('name');
        //     $table->string('symbol', 10);
        //     $table->decimal('exchange_rate', 12, 4)->default(1.0);
        //     $table->timestamps();
        // });

        // // -----------------------------
        // // User Groups
        // // -----------------------------
        // Schema::create('user_groups', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code')->unique();
        //     $table->timestamps();
        // });

        // Schema::create('user_group_translations', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('user_group_id')->constrained()->cascadeOnDelete();
        //     $table->foreignId('language_id')->constrained()->cascadeOnDelete();
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // // -----------------------------
        // // Users
        // // -----------------------------
        // Schema::create('users', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('email')->unique();
        //     $table->string('password');
        //     $table->foreignId('user_group_id')->nullable()->constrained()->nullOnDelete();
        //     $table->foreignId('default_language_id')->nullable()->constrained('languages')->nullOnDelete();
        //     $table->foreignId('default_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
        //     $table->rememberToken();
        //     $table->timestamps();
        // });

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
            $table->string('sku')->unique();
            $table->foreignId('default_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('slug')->unique();
            $table->decimal('weight', 12, 2)->nullable();
            $table->integer('stock')->default(0);
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

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['product_id', 'user_group_id', 'currency_id'], 'unique_product_price');
        });

        // -----------------------------
        // Attributes
        // -----------------------------
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', AttributeTypeEnum::values())->default(AttributeTypeEnum::default()->value);
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
            $table->string('code')->nullable();
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
        // Product Variants
        // -----------------------------
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('weight', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // -----------------------------
        // Carts
        // -----------------------------
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
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
        // Orders
        // -----------------------------
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_no')->unique();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
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
            $table->string('code')->unique()->nullable();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_user_group');
        Schema::dropIfExists('promotion_rules');
        Schema::dropIfExists('promotion_translations');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('attribute_value_translations');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attribute_translations');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
        // Schema::dropIfExists('users');
        // Schema::dropIfExists('user_group_translations');
        // Schema::dropIfExists('user_groups');
        // Schema::dropIfExists('currencies');
        // Schema::dropIfExists('languages');
    }
};
