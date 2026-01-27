<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionRule;
use App\Models\PromotionTranslation;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use App\Models\UserGroup;
use App\Models\UserGroupTranslation;
use App\Models\Zone;
use App\Models\ZoneTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 测试 Observer 删除逻辑.
 *
 * 确保删除主数据时，所有关联数据也被正确删除
 */
class ObserverDeletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试：删除产品时，删除所有关联数据.
     */
    public function test_product_deletion_cascades_to_related_data()
    {
        // 创建产品及其关联数据
        $product = Product::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建产品翻译
        $translation1 = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
        ]);
        $translation2 = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
        ]);

        // 创建产品变体
        $variant1 = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product->id]);

        // 创建产品评价
        $review1 = ProductReview::factory()->create(['product_id' => $product->id]);
        $review2 = ProductReview::factory()->create(['product_id' => $product->id]);

        // 创建分类关联
        $category = Category::factory()->create();
        $product->productCategories()->attach($category->id);

        // 创建属性值关联（需要提供 attribute_id）
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $product->attributeValues()->attach($attributeValue->id, ['attribute_id' => $attribute->id]);

        // 删除产品
        $product->delete();

        // 验证产品已删除
        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        // 验证产品翻译已删除
        $this->assertDatabaseMissing('product_translations', ['id' => $translation1->id]);
        $this->assertDatabaseMissing('product_translations', ['id' => $translation2->id]);

        // 验证产品变体已删除
        $this->assertDatabaseMissing('product_variants', ['id' => $variant1->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant2->id]);

        // 验证产品评价已删除
        $this->assertDatabaseMissing('product_reviews', ['id' => $review1->id]);
        $this->assertDatabaseMissing('product_reviews', ['id' => $review2->id]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('product_category', [
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseMissing('product_attribute_value', [
            'product_id' => $product->id,
            'attribute_value_id' => $attributeValue->id,
        ]);
    }

    /**
     * 测试：删除分类时，递归删除子分类和关联数据.
     */
    public function test_category_deletion_cascades_to_children_and_related_data()
    {
        // 创建父分类
        $parentCategory = Category::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建子分类
        $childCategory1 = Category::factory()->create(['parent_id' => $parentCategory->id]);
        $childCategory2 = Category::factory()->create(['parent_id' => $parentCategory->id]);

        // 创建子分类的子分类
        $grandchildCategory = Category::factory()->create(['parent_id' => $childCategory1->id]);

        // 创建分类翻译
        $parentTranslation = CategoryTranslation::factory()->create([
            'category_id' => $parentCategory->id,
            'language_id' => $language->id,
        ]);
        $childTranslation = CategoryTranslation::factory()->create([
            'category_id' => $childCategory1->id,
            'language_id' => $language->id,
        ]);

        // 创建产品关联
        $product = Product::factory()->create();
        $parentCategory->productCategories()->attach($product->id);

        // 删除父分类
        $parentCategory->delete();

        // 验证父分类已删除
        $this->assertDatabaseMissing('categories', ['id' => $parentCategory->id]);

        // 验证子分类已递归删除
        $this->assertDatabaseMissing('categories', ['id' => $childCategory1->id]);
        $this->assertDatabaseMissing('categories', ['id' => $childCategory2->id]);
        $this->assertDatabaseMissing('categories', ['id' => $grandchildCategory->id]);

        // 验证分类翻译已删除
        $this->assertDatabaseMissing('category_translations', ['id' => $parentTranslation->id]);
        $this->assertDatabaseMissing('category_translations', ['id' => $childTranslation->id]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('product_category', [
            'category_id' => $parentCategory->id,
            'product_id' => $product->id,
        ]);
    }

    /**
     * 测试：删除属性时，删除属性值和关联数据.
     */
    public function test_attribute_deletion_cascades_to_attribute_values()
    {
        $attribute = Attribute::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建属性翻译
        $translation = AttributeTranslation::factory()->create([
            'attribute_id' => $attribute->id,
            'language_id' => $language->id,
        ]);

        // 创建属性值
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        // 创建属性值翻译
        $valueTranslation1 = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $value1->id,
            'language_id' => $language->id,
        ]);

        // 创建产品关联（需要提供 attribute_id）
        $product = Product::factory()->create();
        $product->attributeValues()->attach($value1->id, ['attribute_id' => $attribute->id]);

        // 删除属性
        $attribute->delete();

        // 验证属性已删除
        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);

        // 验证属性翻译已删除
        $this->assertDatabaseMissing('attribute_translations', ['id' => $translation->id]);

        // 验证属性值已删除
        $this->assertDatabaseMissing('attribute_values', ['id' => $value1->id]);
        $this->assertDatabaseMissing('attribute_values', ['id' => $value2->id]);

        // 验证属性值翻译已删除
        $this->assertDatabaseMissing('attribute_value_translations', ['id' => $valueTranslation1->id]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('product_attribute_value', [
            'product_id' => $product->id,
            'attribute_value_id' => $value1->id,
        ]);
    }

    /**
     * 测试：删除属性值时，删除关联数据.
     */
    public function test_attribute_value_deletion_cascades_to_related_data()
    {
        // 创建属性值（先创建属性）
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $language = \App\Models\Language::factory()->create();

        // 创建属性值翻译
        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'language_id' => $language->id,
        ]);

        // 创建产品关联（需要提供 attribute_id）
        $product = Product::factory()->create();
        $product->attributeValues()->attach($attributeValue->id, ['attribute_id' => $attribute->id]);

        // 保存 ID 用于后续验证
        $attributeValueId = $attributeValue->id;
        $translationId = $translation->id;
        $productId = $product->id;

        // 删除属性值
        $attributeValue->delete();

        // 验证属性值已删除
        $this->assertDatabaseMissing('attribute_values', ['id' => $attributeValueId]);

        // 验证属性值翻译已删除
        $this->assertDatabaseMissing('attribute_value_translations', ['id' => $translationId]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('product_attribute_value', [
            'product_id' => $productId,
            'attribute_value_id' => $attributeValueId,
        ]);
    }

    /**
     * 测试：删除规格时，删除规格值和关联数据.
     */
    public function test_specification_deletion_cascades_to_specification_values()
    {
        $specification = Specification::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建规格翻译
        $translation = SpecificationTranslation::factory()->create([
            'specification_id' => $specification->id,
            'language_id' => $language->id,
        ]);

        // 创建规格值
        $value1 = SpecificationValue::factory()->create(['specification_id' => $specification->id]);
        $value2 = SpecificationValue::factory()->create(['specification_id' => $specification->id]);

        // 创建规格值翻译
        $valueTranslation = SpecificationValueTranslation::factory()->create([
            'specification_value_id' => $value1->id,
            'language_id' => $language->id,
        ]);

        // 创建产品变体关联（需要提供 specification_id）
        $productVariant = ProductVariant::factory()->create();
        $productVariant->specificationValues()->attach($value1->id, ['specification_id' => $specification->id]);

        // 删除规格
        $specification->delete();

        // 验证规格已删除
        $this->assertDatabaseMissing('specifications', ['id' => $specification->id]);

        // 验证规格翻译已删除
        $this->assertDatabaseMissing('specification_translations', ['id' => $translation->id]);

        // 验证规格值已删除
        $this->assertDatabaseMissing('specification_values', ['id' => $value1->id]);
        $this->assertDatabaseMissing('specification_values', ['id' => $value2->id]);

        // 验证规格值翻译已删除
        $this->assertDatabaseMissing('specification_value_translations', ['id' => $valueTranslation->id]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('product_variant_specification_value', [
            'product_variant_id' => $productVariant->id,
            'specification_value_id' => $value1->id,
        ]);
    }

    /**
     * 测试：删除规格值时，删除关联数据.
     */
    public function test_specification_value_deletion_cascades_to_related_data()
    {
        // 创建规格值（先创建规格）
        $specification = Specification::factory()->create();
        $specificationValue = SpecificationValue::factory()->create(['specification_id' => $specification->id]);
        $language = \App\Models\Language::factory()->create();

        // 创建规格值翻译
        $translation = SpecificationValueTranslation::factory()->create([
            'specification_value_id' => $specificationValue->id,
            'language_id' => $language->id,
        ]);

        // 创建产品变体关联（需要提供 specification_id）
        $productVariant = ProductVariant::factory()->create();
        $productVariant->specificationValues()->attach($specificationValue->id, ['specification_id' => $specification->id]);

        // 保存 ID 用于后续验证
        $specificationValueId = $specificationValue->id;
        $translationId = $translation->id;
        $productVariantId = $productVariant->id;

        // 删除规格值
        $specificationValue->delete();

        // 验证规格值已删除
        $this->assertDatabaseMissing('specification_values', ['id' => $specificationValueId]);

        // 验证规格值翻译已删除
        $this->assertDatabaseMissing('specification_value_translations', ['id' => $translationId]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('product_variant_specification_value', [
            'product_variant_id' => $productVariantId,
            'specification_value_id' => $specificationValueId,
        ]);
    }

    /**
     * 测试：删除文章时，删除文章翻译.
     */
    public function test_article_deletion_cascades_to_translations()
    {
        $article = Article::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建文章翻译（使用不同的语言避免唯一约束冲突）
        $language2 = \App\Models\Language::factory()->create();
        $translation1 = ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'language_id' => $language->id,
        ]);
        $translation2 = ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'language_id' => $language2->id,
        ]);

        // 删除文章
        $article->delete();

        // 验证文章已删除
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);

        // 验证文章翻译已删除
        $this->assertDatabaseMissing('article_translations', ['id' => $translation1->id]);
        $this->assertDatabaseMissing('article_translations', ['id' => $translation2->id]);
    }

    /**
     * 测试：删除促销时，删除促销规则和关联数据.
     */
    public function test_promotion_deletion_cascades_to_rules_and_related_data()
    {
        $promotion = Promotion::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建促销规则
        $rule1 = PromotionRule::factory()->create(['promotion_id' => $promotion->id]);
        $rule2 = PromotionRule::factory()->create(['promotion_id' => $promotion->id]);

        // 创建促销翻译
        $translation = PromotionTranslation::factory()->create([
            'promotion_id' => $promotion->id,
            'language_id' => $language->id,
        ]);

        // 创建用户组关联
        $userGroup = UserGroup::factory()->create();
        $promotion->userGroups()->attach($userGroup->id);

        // 创建产品变体关联（需要提供 product_id）
        $product = Product::factory()->create();
        $productVariant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $promotion->productVariants()->attach($productVariant->id, ['product_id' => $product->id]);

        // 删除促销
        $promotion->delete();

        // 验证促销已删除
        $this->assertDatabaseMissing('promotions', ['id' => $promotion->id]);

        // 验证促销规则已删除
        $this->assertDatabaseMissing('promotion_rules', ['id' => $rule1->id]);
        $this->assertDatabaseMissing('promotion_rules', ['id' => $rule2->id]);

        // 验证促销翻译已删除
        $this->assertDatabaseMissing('promotion_translations', ['id' => $translation->id]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('promotion_user_group', [
            'promotion_id' => $promotion->id,
            'user_group_id' => $userGroup->id,
        ]);
        $this->assertDatabaseMissing('promotion_product_variant', [
            'promotion_id' => $promotion->id,
            'product_variant_id' => $productVariant->id,
        ]);
    }

    /**
     * 测试：删除国家时，删除地区和关联数据.
     */
    public function test_country_deletion_cascades_to_zones()
    {
        $country = Country::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建国家翻译
        $translation = CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'language_id' => $language->id,
        ]);

        // 创建地区
        $zone1 = Zone::factory()->create(['country_id' => $country->id]);
        $zone2 = Zone::factory()->create(['country_id' => $country->id]);

        // 创建地区翻译
        $zoneTranslation = ZoneTranslation::factory()->create([
            'zone_id' => $zone1->id,
            'language_id' => $language->id,
        ]);

        // 删除国家
        $country->delete();

        // 验证国家已删除
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);

        // 验证国家翻译已删除
        $this->assertDatabaseMissing('country_translations', ['id' => $translation->id]);

        // 验证地区已删除
        $this->assertDatabaseMissing('zones', ['id' => $zone1->id]);
        $this->assertDatabaseMissing('zones', ['id' => $zone2->id]);

        // 验证地区翻译已删除
        $this->assertDatabaseMissing('zone_translations', ['id' => $zoneTranslation->id]);
    }

    /**
     * 测试：删除地区时，删除地区翻译.
     */
    public function test_zone_deletion_cascades_to_translations()
    {
        $zone = Zone::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建地区翻译（使用不同的语言避免唯一约束冲突）
        $language2 = \App\Models\Language::factory()->create();
        $translation1 = ZoneTranslation::factory()->create([
            'zone_id' => $zone->id,
            'language_id' => $language->id,
        ]);
        $translation2 = ZoneTranslation::factory()->create([
            'zone_id' => $zone->id,
            'language_id' => $language2->id,
        ]);

        // 删除地区
        $zone->delete();

        // 验证地区已删除
        $this->assertDatabaseMissing('zones', ['id' => $zone->id]);

        // 验证地区翻译已删除
        $this->assertDatabaseMissing('zone_translations', ['id' => $translation1->id]);
        $this->assertDatabaseMissing('zone_translations', ['id' => $translation2->id]);
    }

    /**
     * 测试：删除产品变体时，删除关联数据.
     */
    public function test_product_variant_deletion_cascades_to_related_data()
    {
        // 创建产品和产品变体
        $product = Product::factory()->create();
        $productVariant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // 创建产品变体评价
        $review1 = ProductReview::factory()->create(['product_variants' => $productVariant->id]);
        $review2 = ProductReview::factory()->create(['product_variants' => $productVariant->id]);

        // 创建规格值关联（需要提供 specification_id）
        $specification = Specification::factory()->create();
        $specificationValue = SpecificationValue::factory()->create(['specification_id' => $specification->id]);
        $productVariant->specificationValues()->attach($specificationValue->id, ['specification_id' => $specification->id]);

        // 创建促销关联（需要提供 product_id）
        $promotion = Promotion::factory()->create();
        $productVariant->promotions()->attach($promotion->id, ['product_id' => $product->id]);

        // 保存 ID 用于后续验证
        $productVariantId = $productVariant->id;
        $specificationValueId = $specificationValue->id;
        $promotionId = $promotion->id;
        $review1Id = $review1->id;
        $review2Id = $review2->id;

        // 删除产品变体
        $productVariant->delete();

        // 验证产品变体已删除
        $this->assertDatabaseMissing('product_variants', ['id' => $productVariantId]);

        // 验证产品变体评价已删除
        $this->assertDatabaseMissing('product_reviews', ['id' => $review1Id]);
        $this->assertDatabaseMissing('product_reviews', ['id' => $review2Id]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('product_variant_specification_value', [
            'product_variant_id' => $productVariantId,
            'specification_value_id' => $specificationValueId,
        ]);
        $this->assertDatabaseMissing('promotion_product_variant', [
            'product_variant_id' => $productVariantId,
            'promotion_id' => $promotionId,
        ]);
    }

    /**
     * 测试：删除用户组时，删除关联数据.
     */
    public function test_user_group_deletion_cascades_to_related_data()
    {
        $userGroup = UserGroup::factory()->create();
        $language = \App\Models\Language::factory()->create();

        // 创建用户组翻译（手动创建，因为没有 Factory）
        $translation = UserGroupTranslation::create([
            'user_group_id' => $userGroup->id,
            'language_id' => $language->id,
            'name' => 'Test User Group',
        ]);

        // 创建促销关联
        $promotion = Promotion::factory()->create();
        $promotion->userGroups()->attach($userGroup->id);

        // 删除用户组
        $userGroup->delete();

        // 验证用户组已删除
        $this->assertDatabaseMissing('user_groups', ['id' => $userGroup->id]);

        // 验证用户组翻译已删除
        $this->assertDatabaseMissing('user_group_translations', ['id' => $translation->id]);

        // 验证中间表关联已删除
        $this->assertDatabaseMissing('promotion_user_group', [
            'user_group_id' => $userGroup->id,
            'promotion_id' => $promotion->id,
        ]);
    }
}
