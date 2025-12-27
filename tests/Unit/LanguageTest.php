<?php

namespace Tests\Unit;

use App\Models\Language;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_can_be_created_using_factory()
    {
        $language = Language::factory()->create();

        $this->assertNotNull($language);
        $this->assertInstanceOf(Language::class, $language);
        $this->assertIsString($language->code);
        $this->assertIsString($language->name);
    }

    public function test_default_attribute_casting()
    {
        $language = Language::factory()->create(['default' => true]);

        $this->assertIsBool($language->default);
        $this->assertTrue($language->default);
    }

    public function test_category_translations_relationship()
    {
        $language = new Language;
        $relation = $language->categoryTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('language_id', $relation->getForeignKeyName());
    }

    public function test_product_translations_relationship()
    {
        $language = new Language;
        $relation = $language->productTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('language_id', $relation->getForeignKeyName());
    }
}
