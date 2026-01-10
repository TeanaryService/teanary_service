<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created_using_factory()
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category);
        $this->assertInstanceOf(Category::class, $category);
        $this->assertIsString($category->slug);
    }

    public function test_category_relationship()
    {
        $category = new Category;
        $relation = $category->category();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('parent_id', $relation->getForeignKeyName());
    }

    public function test_categories_relationship()
    {
        $category = new Category;
        $relation = $category->categories();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('parent_id', $relation->getForeignKeyName());
    }

    public function test_category_translations_relationship()
    {
        $category = new Category;
        $relation = $category->categoryTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('category_id', $relation->getForeignKeyName());
    }

    public function test_product_categories_relationship()
    {
        $category = new Category;
        $relation = $category->productCategories();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('product_category', $relation->getTable());
        $this->assertEquals('category_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('product_id', $relation->getRelatedPivotKeyName());
    }

}
