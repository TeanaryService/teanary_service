<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Services\LocaleCurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_redirects_to_default_language_home_page()
    {
        $language = Language::factory()->create(['code' => 'en', 'default' => true]);

        $response = $this->get('/');

        $response->assertRedirect(route('home', ['locale' => 'en']));
    }

    #[Test]
    public function it_displays_home_page_with_valid_locale()
    {
        $language = Language::factory()->create(['code' => 'en', 'default' => true]);

        $response = $this->get('/en');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_returns_404_for_invalid_locale()
    {
        $response = $this->get('/invalid-locale');

        $response->assertStatus(404);
    }
}

