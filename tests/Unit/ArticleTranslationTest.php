<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTranslationTest extends TestCase
{
    use RefreshDatabase; // 确保每次测试后数据库状态干净

    /**
     * Test if an ArticleTranslation instance can be created using the factory.
     */
    public function test_article_translation_can_be_created_using_factory()
    {
        $articleTranslation = ArticleTranslation::factory()->create();

        $this->assertNotNull($articleTranslation);
        $this->assertInstanceOf(ArticleTranslation::class, $articleTranslation);
        $this->assertIsInt($articleTranslation->article_id);
        $this->assertIsString($articleTranslation->language_id);
        $this->assertIsString($articleTranslation->title);
    }

    /**
     * Test the 'article_id' attribute casting.
     */
    public function test_article_id_attribute_casting()
    {
        $articleTranslation = ArticleTranslation::factory()->create([
            'article_id' => 123,
        ]);

        $this->assertIsInt($articleTranslation->article_id);
        $this->assertEquals(123, $articleTranslation->article_id);
    }

    /**
     * Test the 'article' relationship.
     */
    public function test_article_relationship()
    {
        $articleTranslation = new ArticleTranslation;
        $relation = $articleTranslation->article();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('article_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }
}
