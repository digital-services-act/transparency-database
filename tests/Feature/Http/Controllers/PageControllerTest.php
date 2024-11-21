<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test markdown files
        $markdownDir = __DIR__ . '/../../../../resources/markdown';
        $testfile = $markdownDir . '/test-page.md';

        if (!File::exists($testfile)) {
            File::put($testfile, "# Test Page\nThis is a test page content.");
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up test markdown files
        $markdownDir = __DIR__ . '/../../../../resources/markdown';
        $testfile = $markdownDir . '/test-page.md';
        File::delete($testfile);
    }

    public function test_show_displays_markdown_page(): void
    {
        $response = $this->get('/page/test-page');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertSee('Test Page');
        $response->assertSee('This is a test page content');
        $response->assertViewHas('page_title', 'Test Page');
        $response->assertViewHas('breadcrumb', 'Test Page');
        $response->assertViewHas('show_feedback_link', false);
    }

    public function test_show_sanitizes_page_name(): void
    {
        // Create a test file with sanitized name
        $markdownDir = __DIR__ . '/../../../../resources/markdown';
        File::put($markdownDir . '/testpage.md', "# Test Page\nContent.");

        // Try to access with unsanitized name
        $response = $this->get('/page/Test..Page!!!');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertSee('Test Page');
    }

    public function test_show_handles_redirects(): void
    {
        $response = $this->get('/page/cookie-policy');
        $response->assertRedirect('https://commission.europa.eu/cookies-policy_en');

        $response = $this->get('/page/latest-updates');
        $response->assertRedirect('/');
    }

    public function test_show_returns_404_for_nonexistent_page(): void
    {
        $response = $this->get('/page/nonexistent-page');
        $response->assertStatus(404);
    }

    public function test_show_modifies_page_titles(): void
    {
        // Create a test file
        $markdownDir = __DIR__ . '/../../../../resources/markdown';
        File::put($markdownDir . '/api-documentation.md', "# API Documentation\nContent.");

        $response = $this->get('/page/api-documentation');

        $response->assertStatus(200);
        $response->assertViewHas('page_title', 'API Documentation');
        $response->assertViewHas('breadcrumb', 'API Documentation');
    }

    public function test_show_handles_feedback_link(): void
    {
        // Create a FAQ page
        $markdownDir = __DIR__ . '/../../../../resources/markdown';
        File::put($markdownDir . '/faq.md', "# FAQ\nContent.");

        $response = $this->get('/page/faq');

        $response->assertStatus(200);
        $response->assertViewHas('show_feedback_link', true);
    }

    public function test_profile_show_sets_profile_flag(): void
    {
        $this->signIn();
        // Create a test file
        $markdownDir = __DIR__ . '/../../../../resources/markdown';
        File::put($markdownDir . '/test-profile.md', "# Test Profile\nContent.");


        $response = $this->get('/profile/page/test-profile');

        $response->assertStatus(200);
        $response->assertViewHas('profile', true);
    }

    public function test_markdown_conversion_adds_header_ids(): void
    {
        $this->signIn();
        // Create a test file with multiple headers
        $markdownDir = __DIR__ . '/../../../../resources/markdown';
        $markdown = "# Main Title\n## Sub Section\n### Another Section";
        File::put($markdownDir . '/headers.md', $markdown);

        $response = $this->get('/page/headers');

        $response->assertStatus(200);
        $response->assertSee('id="main-title"', false);
        $response->assertSee('id="sub-section"', false);
        $response->assertSee('id="another-section"', false);
    }
}
