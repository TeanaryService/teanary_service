<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\Currency;
use App\Models\UserGroup;
use App\Models\User;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionTranslation;
use App\Models\PromotionRule;

class FullSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Languages
         */
        $languages = collect([
            ['code' => 'en', 'name' => 'English', 'default' => true],
            ['code' => 'zh_CN', 'name' => '中文', 'default' => false],
        ])->map(fn($data) => Language::create($data));

        /**
         * Currencies
         */
        $currencies = collect([
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'default' => true],
            ['code' => 'CNY', 'name' => '人民币', 'symbol' => '¥', 'default' => false],
        ])->map(fn($data) => Currency::create($data));

        /**
         * User Groups
         */
        $userGroups = collect([
            ['name' => 'Retail'],
            ['name' => 'Wholesale'],
        ])->map(function ($group) use ($languages) {
            $ug = UserGroup::create();
            foreach ($languages as $lang) {
                $ug->userGroupTranslations()->create([
                    'language_id' => $lang->id,
                    'name' => $group['name'] . ' (' . $lang->code . ')',
                ]);
            }
            return $ug;
        });

        /**
         * Users
         */
        User::factory(10)->create();

        /**
         * Categories
         */
        $categories = collect([
            ['slug' => 'flowers'],
            ['slug' => 'bouquets'],
        ])->map(function ($cat) use ($languages) {
            $category = Category::create($cat);

            //添加图片
            $image = generateRandomImage();

            $category->addMedia($image)
                ->preservingOriginal()
                ->toMediaCollection('image');

            foreach ($languages as $lang) {
                CategoryTranslation::create([
                    'category_id' => $category->id,
                    'language_id' => $lang->id,
                    'name' => ucfirst($cat['slug']) . ' ' . $lang->code,
                    'description' => 'Description for ' . $cat['slug'] . ' in ' . $lang->code,
                ]);
            }
            return $category;
        });

        /**
         * Attributes
         */
        $attributes = collect([
            ['label' => 'stem_length'],
            ['label' => 'origin'],
        ])->map(function ($attr) use ($languages) {
            $a = Attribute::create(); // 不再插入 code

            foreach ($languages as $lang) {
                AttributeTranslation::create([
                    'attribute_id' => $a->id,
                    'language_id' => $lang->id,
                    'name' => ucfirst($attr['label']) . ' ' . $lang->code,
                ]);
            }

            return $a;
        });

        /**
         * Attribute Values
         */
        $attributeValues = $attributes->flatMap(function ($attr) use ($languages) {
            return collect(['Short', 'Medium', 'Long'])->map(function ($value) use ($attr, $languages) {
                $av = AttributeValue::create([
                    'attribute_id' => $attr->id,
                ]);
                foreach ($languages as $lang) {
                    AttributeValueTranslation::create([
                        'attribute_value_id' => $av->id,
                        'language_id' => $lang->id,
                        'name' => $value . ' ' . $lang->code,
                    ]);
                }
                return $av;
            });
        });

        /**
         * Specifications
         */
        $specifications = collect([
            ['label' => 'color'],
            ['label' => 'size'],
        ])->map(function ($spec) use ($languages) {
            $s = Specification::create();
            foreach ($languages as $lang) {
                SpecificationTranslation::create([
                    'specification_id' => $s->id,
                    'language_id' => $lang->id,
                    'name' => ucfirst($spec['label']) . ' ' . $lang->code,
                ]);
            }
            return $s;
        });

        /**
         * Specification Values
         */
        $specificationValues = $specifications->flatMap(function ($spec) use ($languages) {
            $values = match ($spec->code) {
                'color' => ['Red', 'Yellow', 'White'],
                'size' => ['Small', 'Medium', 'Large'],
                default => ['Default'],
            };

            return collect($values)->map(function ($value) use ($spec, $languages) {
                $sv = SpecificationValue::create([
                    'specification_id' => $spec->id,
                ]);
                foreach ($languages as $lang) {
                    SpecificationValueTranslation::create([
                        'specification_value_id' => $sv->id,
                        'language_id' => $lang->id,
                        'name' => $value . ' ' . $lang->code,
                    ]);
                }
                return $sv;
            });
        });

        /**
         * Products
         */
        $products = collect(range(1, 10))->map(function ($i) use (
            $languages,
            $categories,
            $userGroups,
            $currencies,
            $specifications,
            $specificationValues
        ) {
            // 创建产品 (SPU)
            $product = Product::create([
                'slug' => 'product-' . $i,
                'status' => \App\Enums\ProductStatusEnum::default(),
            ]);
            //添加图片
            $this->attachRandomImagesToProduct($product);

            $product->productCategories()->attach($categories->random()->id);

            // 多语言翻译
            foreach ($languages as $lang) {
                ProductTranslation::create([
                    'product_id' => $product->id,
                    'language_id' => $lang->id,
                    'name' => "Product $i " . $lang->code,
                    'short_description' => "Short description $i in " . $lang->code,
                    'description' => "Long description $i in " . $lang->code,
                ]);
            }

            $attributeValues = AttributeValue::with('attribute') // 确保能取到 attribute_id
                ->inRandomOrder()
                ->take(rand(1, 3))
                ->get();

            $product->attributeValues()->attach(
                $attributeValues->mapWithKeys(function ($value) {
                    return [
                        $value->id => ['attribute_id' => $value->attribute_id],
                    ];
                })->toArray()
            );


            // Assign random specifications
            $specsUsed = $specifications->random(rand(1, 2));

            // Create variants
            for ($v = 1; $v <= 3; $v++) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'P-' . $i . '-V' . $v,
                    'price' => rand(15, 150),
                    'stock' => rand(1, 20),
                    'weight' => rand(50, 200),
                ]);

                // Link specification values
                foreach ($specsUsed as $spec) {
                    $value = $specificationValues
                        ->where('specification_id', $spec->id)
                        ->random();

                    $variant->specificationValues()->attach($value->id, ['specification_id' => $value->specification_id]);
                }
            }

            return $product;
        });

        /**
         * Promotions
         */
        $promotions = collect([
            ['type' => \App\Enums\PromotionTypeEnum::Coupon],
        ])->map(function ($promo) use ($languages, $userGroups, $products) {
            $promotion = Promotion::create(array_merge($promo, [
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(10),
                'active' => true,
            ]));

            foreach ($languages as $lang) {
                PromotionTranslation::create([
                    'promotion_id' => $promotion->id,
                    'language_id' => $lang->id,
                    'name' => $promo['type']->value . ' Name ' . $lang->code,
                    'description' => 'Description of ' . $promo['type']->value . ' in ' . $lang->code,
                ]);
            }

            PromotionRule::create([
                'promotion_id' => $promotion->id,
                'condition_type' => \App\Enums\PromotionConditionTypeEnum::OrderTotalMin,
                'condition_value' => 50,
                'discount_type' => \App\Enums\PromotionDiscountTypeEnum::Fixed,
                'discount_value' => 10,
            ]);

            $promotion->userGroups()->sync($userGroups->pluck('id')->toArray());

            $product = $products->random();
            $promotion->productVariants()->attach($product->productVariants->first()->id, ['product_id' => $product->id]);

            return $promotion;
        });
    }

    private function attachRandomImagesToProduct($product, $min = 2, $max = 4)
    {
        $count = rand($min, $max);

        for ($i = 0; $i < $count; $i++) {
            $image = generateRandomImage();

            $product->addMedia($image)
                ->preservingOriginal()
                ->toMediaCollection('images');
        }
    }
}
