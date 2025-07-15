<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Currency;
use App\Models\UserGroup;
use App\Models\UserGroupTranslation;
use App\Models\User;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Promotion;
use App\Models\PromotionTranslation;
use App\Models\PromotionRule;
use App\Models\PromotionUserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FullSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------
        // Languages
        // ----------------------------
        $en = Language::create([
            'code' => 'en',
            'name' => 'English',
        ]);

        $zh = Language::create([
            'code' => 'zh',
            'name' => '中文',
        ]);

        // ----------------------------
        // Currencies
        // ----------------------------
        $usd = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 1.0,
        ]);

        $cny = Currency::create([
            'code' => 'CNY',
            'name' => '人民币',
            'symbol' => '¥',
            'exchange_rate' => 7.0,
        ]);

        // ----------------------------
        // User Groups
        // ----------------------------
        $retailGroup = UserGroup::create([
            'code' => 'retail',
        ]);

        UserGroupTranslation::create([
            'user_group_id' => $retailGroup->id,
            'language_id' => $en->id,
            'name' => 'Retail Customer',
        ]);

        UserGroupTranslation::create([
            'user_group_id' => $retailGroup->id,
            'language_id' => $zh->id,
            'name' => '零售客户',
        ]);

        $vipGroup = UserGroup::create([
            'code' => 'vip',
        ]);

        UserGroupTranslation::create([
            'user_group_id' => $vipGroup->id,
            'language_id' => $en->id,
            'name' => 'VIP Customer',
        ]);

        UserGroupTranslation::create([
            'user_group_id' => $vipGroup->id,
            'language_id' => $zh->id,
            'name' => 'VIP客户',
        ]);

        // ----------------------------
        // Users
        // ----------------------------
        User::create([
            'name' => 'John Retail',
            'email' => 'chat@miexin.com',
            'password' => Hash::make('dylfj22649978'),
            'user_group_id' => $retailGroup->id,
            'default_language_id' => $en->id,
            'default_currency_id' => $usd->id,
        ]);

        User::create([
            'name' => 'Lucy VIP',
            'email' => 'vip@example.com',
            'password' => Hash::make('password'),
            'user_group_id' => $vipGroup->id,
            'default_language_id' => $zh->id,
            'default_currency_id' => $cny->id,
        ]);

        // ----------------------------
        // Categories
        // ----------------------------
        $categories = [
            [
                'slug' => 'roses',
                'en' => 'Roses',
                'zh' => '玫瑰花',
            ],
            [
                'slug' => 'lilies',
                'en' => 'Lilies',
                'zh' => '百合花',
            ],
            [
                'slug' => 'tulips',
                'en' => 'Tulips',
                'zh' => '郁金香',
            ],
        ];

        $categoryRecords = [];

        foreach ($categories as $cat) {
            $category = Category::create([
                'slug' => $cat['slug'],
            ]);

            CategoryTranslation::create([
                'category_id' => $category->id,
                'language_id' => $en->id,
                'name' => $cat['en'],
                'description' => "Beautiful {$cat['en']}",
            ]);

            CategoryTranslation::create([
                'category_id' => $category->id,
                'language_id' => $zh->id,
                'name' => $cat['zh'],
                'description' => "美丽的{$cat['zh']}",
            ]);

            $categoryRecords[$cat['slug']] = $category;
        }

        // ----------------------------
        // Attributes
        // ----------------------------
        $colorAttr = Attribute::create([
            'code' => 'color',
            'type' => 'select',
        ]);

        AttributeTranslation::create([
            'attribute_id' => $colorAttr->id,
            'language_id' => $en->id,
            'name' => 'Color',
        ]);

        AttributeTranslation::create([
            'attribute_id' => $colorAttr->id,
            'language_id' => $zh->id,
            'name' => '颜色',
        ]);

        $stemAttr = Attribute::create([
            'code' => 'stem_count',
            'type' => 'select',
        ]);

        AttributeTranslation::create([
            'attribute_id' => $stemAttr->id,
            'language_id' => $en->id,
            'name' => 'Stem Count',
        ]);

        AttributeTranslation::create([
            'attribute_id' => $stemAttr->id,
            'language_id' => $zh->id,
            'name' => '支数',
        ]);

        // ----------------------------
        // Attribute Values
        // ----------------------------
        $colors = [
            ['code' => 'red', 'en' => 'Red', 'zh' => '红色'],
            ['code' => 'white', 'en' => 'White', 'zh' => '白色'],
            ['code' => 'pink', 'en' => 'Pink', 'zh' => '粉色'],
        ];

        $colorValues = [];

        foreach ($colors as $color) {
            $v = AttributeValue::create([
                'attribute_id' => $colorAttr->id,
                'code' => $color['code'],
            ]);

            AttributeValueTranslation::create([
                'attribute_value_id' => $v->id,
                'language_id' => $en->id,
                'name' => $color['en'],
            ]);

            AttributeValueTranslation::create([
                'attribute_value_id' => $v->id,
                'language_id' => $zh->id,
                'name' => $color['zh'],
            ]);

            $colorValues[$color['code']] = $v;
        }

        $stems = [
            ['code' => '11', 'en' => '11 stems', 'zh' => '11支'],
            ['code' => '33', 'en' => '33 stems', 'zh' => '33支'],
            ['code' => '66', 'en' => '66 stems', 'zh' => '66支'],
        ];

        $stemValues = [];

        foreach ($stems as $stem) {
            $v = AttributeValue::create([
                'attribute_id' => $stemAttr->id,
                'code' => $stem['code'],
            ]);

            AttributeValueTranslation::create([
                'attribute_value_id' => $v->id,
                'language_id' => $en->id,
                'name' => $stem['en'],
            ]);

            AttributeValueTranslation::create([
                'attribute_value_id' => $v->id,
                'language_id' => $zh->id,
                'name' => $stem['zh'],
            ]);

            $stemValues[$stem['code']] = $v;
        }

        // ----------------------------
        // Products (multiple)
        // ----------------------------
        $productNames = [
            [
                'sku_prefix' => 'ROSE',
                'name_en' => 'Rose Bouquet',
                'name_zh' => '玫瑰花束',
                'category_slug' => 'roses',
            ],
            [
                'sku_prefix' => 'LILY',
                'name_en' => 'Lily Bouquet',
                'name_zh' => '百合花束',
                'category_slug' => 'lilies',
            ],
            [
                'sku_prefix' => 'TULIP',
                'name_en' => 'Tulip Bouquet',
                'name_zh' => '郁金香花束',
                'category_slug' => 'tulips',
            ],
        ];

        foreach ($productNames as $productData) {
            $product = Product::create([
                'sku' => $productData['sku_prefix'] . '-BASE',
                'default_currency_id' => $usd->id,
                'slug' => Str::slug($productData['name_en']),
                'weight' => 0.5,
                'stock' => 100,
                'status' => 'active',
            ]);

            ProductTranslation::create([
                'product_id' => $product->id,
                'language_id' => $en->id,
                'name' => $productData['name_en'],
                'short_description' => "Beautiful " . $productData['name_en'],
                'description' => "A lovely bouquet of fresh flowers.",
            ]);

            ProductTranslation::create([
                'product_id' => $product->id,
                'language_id' => $zh->id,
                'name' => $productData['name_zh'],
                'short_description' => "美丽的" . $productData['name_zh'],
                'description' => "一束美丽的新鲜花卉。",
            ]);

            ProductCategory::create([
                'product_id' => $product->id,
                'category_id' => $categoryRecords[$productData['category_slug']]->id,
            ]);

            ProductPrice::create([
                'product_id' => $product->id,
                'user_group_id' => $retailGroup->id,
                'currency_id' => $usd->id,
                'price' => rand(8, 15),
            ]);

            ProductPrice::create([
                'product_id' => $product->id,
                'user_group_id' => $vipGroup->id,
                'currency_id' => $usd->id,
                'price' => rand(6, 10),
            ]);

            // create 2 variants per product
            $variants = [
                ['color' => 'red', 'stems' => '11', 'price' => rand(10, 15)],
                ['color' => 'white', 'stems' => '33', 'price' => rand(18, 25)],
            ];

            foreach ($variants as $index => $variant) {
                $variantModel = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $productData['sku_prefix'] . '-' . strtoupper($variant['color']) . '-' . $variant['stems'],
                    'currency_id' => $usd->id,
                    'price' => $variant['price'],
                    'stock' => rand(10, 50),
                    'weight' => 0.6 + $index * 0.2,
                ]);

                ProductVariantValue::create([
                    'product_variant_id' => $variantModel->id,
                    'attribute_value_id' => $colorValues[$variant['color']]->id,
                ]);

                ProductVariantValue::create([
                    'product_variant_id' => $variantModel->id,
                    'attribute_value_id' => $stemValues[$variant['stems']]->id,
                ]);
            }
        }

        // ----------------------------
        // Promotions
        // ----------------------------
        $promotion = Promotion::create([
            'code' => 'SAVE10',
            'type' => 'coupon',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'active' => true,
        ]);

        PromotionTranslation::create([
            'promotion_id' => $promotion->id,
            'language_id' => $en->id,
            'name' => 'Save 10%',
            'description' => 'Save 10% on orders over $100',
        ]);

        PromotionTranslation::create([
            'promotion_id' => $promotion->id,
            'language_id' => $zh->id,
            'name' => '节省10%',
            'description' => '满100美元立减10%',
        ]);

        PromotionRule::create([
            'promotion_id' => $promotion->id,
            'condition_type' => 'order_total_min',
            'condition_value' => 100,
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        PromotionUserGroup::create([
            'promotion_id' => $promotion->id,
            'user_group_id' => $retailGroup->id,
        ]);
    }
}
