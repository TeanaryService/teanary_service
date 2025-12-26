<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionRule;
use App\Models\PromotionTranslation;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FullSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Languages.
         */
        $languages = collect([
            ['code' => 'en',    'name' => 'English',  'default' => true],
            ['code' => 'zh_CN', 'name' => '中文',       'default' => false],
            ['code' => 'es',    'name' => 'Español',  'default' => false],
            ['code' => 'fr',    'name' => 'Français', 'default' => false],
            ['code' => 'de',    'name' => 'Deutsch',  'default' => false],
            ['code' => 'ja',    'name' => '日本語',     'default' => false],
            ['code' => 'ko',    'name' => '한국어',     'default' => false],
            ['code' => 'ru', 'name' => 'Русский', 'default' => false],
        ])->map(fn ($data) => Language::create($data));

        /**
         * Currencies.
         */
        $currencies = collect([
            ['code' => 'USD', 'name' => 'US Dollar',       'symbol' => '$',   'default' => true],
            ['code' => 'EUR', 'name' => 'Euro',            'symbol' => '€',   'default' => false],
            ['code' => 'GBP', 'name' => 'British Pound',   'symbol' => '£',   'default' => false],
            ['code' => 'CNY', 'name' => 'Chinese Yuan',    'symbol' => '¥',   'default' => false],
            ['code' => 'JPY', 'name' => 'Japanese Yen',    'symbol' => '¥',   'default' => false],
            ['code' => 'KRW', 'name' => 'Korean Won',      'symbol' => '₩',   'default' => false],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'default' => false],
        ])->map(fn ($data) => Currency::create($data));

        /**
         * User Groups.
         */
        $userGroups = collect([
            ['name' => 'Retail'],
            ['name' => 'Wholesale'],
        ])->map(function ($group) use ($languages) {
            $ug = UserGroup::create();
            foreach ($languages as $lang) {
                $ug->userGroupTranslations()->create([
                    'language_id' => $lang->id,
                    'name' => $group['name'].' ('.$lang->code.')',
                ]);
            }

            return $ug;
        });

        /*
         * Users
         */
        User::factory(10)->hasArticles()->create();

        /**
         * Categories.
         */
        $categories = collect([
            ['slug' => 'green-tea'],          // 绿茶
            ['slug' => 'black-tea'],          // 红茶（黑茶在国外叫 Black Tea）
            ['slug' => 'oolong-tea'],         // 乌龙茶
            ['slug' => 'pu-erh-tea'],         // 普洱茶
            ['slug' => 'white-tea'],          // 白茶
            // ['slug' => 'flower-tea'],         // 花草茶（如菊花、茉莉）
            // ['slug' => 'tea-gift-boxes'],     // 茶礼盒
            // ['slug' => 'aged-tea'],           // 老茶/陈茶（收藏级）
            // ['slug' => 'tea-tools'],          // 茶具
            // ['slug' => 'sampler-sets'],       // 组合体验装（多款混合试喝）
        ])
            ->map(function ($cat) use ($languages) {
                $category = Category::create($cat);

                // 添加图片
                $image = generateRandomImage();

                $category->addMedia($image)
                    ->preservingOriginal()
                    ->toMediaCollection('image');

                foreach ($languages as $lang) {
                    CategoryTranslation::create([
                        'category_id' => $category->id,
                        'language_id' => $lang->id,
                        'name' => ucfirst($cat['slug']).' '.$lang->code,
                        'description' => 'Description for '.$cat['slug'].' in '.$lang->code,
                    ]);
                }

                return $category;
            });

        /**
         * Attributes.
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
                    'name' => ucfirst($attr['label']).' '.$lang->code,
                ]);
            }

            return $a;
        });

        /**
         * Attribute Values.
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
                        'name' => $value.' '.$lang->code,
                    ]);
                }

                return $av;
            });
        });

        /**
         * Specifications.
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
                    'name' => ucfirst($spec['label']).' '.$lang->code,
                ]);
            }

            return $s;
        });

        /**
         * Specification Values.
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
                        'name' => $value.' '.$lang->code,
                    ]);
                }

                return $sv;
            });
        });

        /**
         * Products.
         */
        $products = collect(range(1, 22))->map(function ($i) use (
            $languages,
            $categories,
            $specifications,
            $specificationValues
        ) {
            // 创建产品 (SPU)
            $product = Product::create([
                'slug' => 'product-'.$i,
                'status' => \App\Enums\ProductStatusEnum::default(),
            ]);
            // 添加图片
            $this->attachRandomImages(model: $product);

            $product->productCategories()->attach($categories->random()->id);

            // 多语言翻译
            foreach ($languages as $lang) {
                $imageUrl = $this->urlImage();
                ProductTranslation::create([
                    'product_id' => $product->id,
                    'language_id' => $lang->id,
                    'name' => "Product $i ".$lang->code,
                    'short_description' => "Short description $i in ".$lang->code,
                    'description' => "Long <img src='$imageUrl'> description $i in ".$lang->code,
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
            for ($v = 1; $v <= 3; ++$v) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'P-'.$i.'-V'.$v,
                    'price' => rand(15, 150),
                    'stock' => rand(1, 20),
                    'weight' => rand(50, 200),
                ]);

                // 添加图片
                $this->attachRandomImages(model: $variant, min: 1, max: 1, collection: 'image');

                // Link specification values
                foreach ($specsUsed as $spec) {
                    $value = $specificationValues
                        ->where('specification_id', $spec->id)
                        ->random();

                    $variant->specificationValues()->attach($value->id, ['specification_id' => $value->specification_id]);
                }
            }

            // 添加 5-10 个评价
            $users = \App\Models\User::all();

            collect(range(1, rand(5, 10)))->each(function () use ($product, $users) {
                $review = \App\Models\ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                    'rating' => rand(1, 5),
                    'content' => fake()->paragraph,
                    'is_approved' => true,
                ]);

                $this->attachRandomImages(model: $review, collection: 'images');
            });

            return $product;
        });

        /**
         * Promotions.
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
                    'name' => $promo['type']->value.' Name '.$lang->code,
                    'description' => 'Description of '.$promo['type']->value.' in '.$lang->code,
                ]);
            }

            $promotionRule = PromotionRule::create([
                'promotion_id' => $promotion->id,
                'condition_type' => \App\Enums\PromotionConditionTypeEnum::OrderTotalMin,
                'condition_value' => 50,
                'discount_type' => \App\Enums\PromotionDiscountTypeEnum::Fixed,
                'discount_value' => 10,
            ]);
            // 添加图片
            $this->attachRandomImages(model: $promotionRule, min: 1, max: 1, collection: 'image');

            $promotion->userGroups()->sync($userGroups->pluck('id')->toArray());

            $product = $products->random();
            $promotion->productVariants()->attach($product->productVariants->first()->id, ['product_id' => $product->id]);

            return $promotion;
        });
    }

    private function attachRandomImages($model, $min = 2, $max = 4, $collection = 'images')
    {
        $count = rand($min, $max);

        for ($i = 0; $i < $count; ++$i) {
            $image = generateRandomImage();

            $model->addMedia($image)
                ->preservingOriginal()
                ->toMediaCollection($collection);
        }
    }

    private function urlImage()
    {
        $image = generateRandomImage();

        $newPath = storage_path('app/public/product/description');
        if (! File::isDirectory($newPath)) {
            File::makeDirectory($newPath);
        }

        $newFile = $newPath.'/'.Str::random(40).'.jpg';
        File::copy($image, $newFile);
        $imageUrl = Str::replace(storage_path('app/public'), 'storage', $newFile);

        return asset($imageUrl);
    }
}
