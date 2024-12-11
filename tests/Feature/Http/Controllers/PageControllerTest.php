<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected string $markdownDir = __DIR__ . '/../../../../resources/markdown';

    public function test_show_displays_markdown_page(): void
    {
        $testfile = "{$this->markdownDir}/test-page.md";
        File::put($testfile, "# Test Page\nThis is a test page content.");

        $response = $this->get('/page/test-page');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertSee('Test Page');
        $response->assertSee('This is a test page content');
        $response->assertViewHas('page_title', 'Test Page');
        $response->assertViewHas('breadcrumb', 'Test Page');
        $response->assertViewHas('show_feedback_link', false);

        File::delete($testfile);
    }

    public function test_show_sanitizes_page_name(): void
    {
        // Create a test file with sanitized name
        $testfile = "{$this->markdownDir}/testpage.md";
        File::put($testfile, "# Test Page\nContent.");

        // Try to access with unsanitized name
        $response = $this->get('/page/Test..Page!!!');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertSee('Test Page');

        File::delete($testfile);
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
        $response = $this->get('/page/api-documentation');

        $response->assertStatus(200);
        $response->assertViewHas('page_title', 'API Documentation');
        $response->assertViewHas('breadcrumb', 'API Documentation');
    }

    public function test_profile_show_sets_profile_flag(): void
    {
        $this->signIn();
    
        // Create a test file 
        $testfile = "{$this->markdownDir}/test-profile.md";
        File::put($testfile, "# Test Profile\nContent.");


        $response = $this->get('/profile/page/test-profile');

        $response->assertStatus(200);
        $response->assertViewHas('profile', true);

        File::delete($testfile);
    }

    public function test_markdown_conversion_adds_header_ids(): void
    {
        $this->signIn();
        // Create a test file with multiple headers
        $markdown = "# Main Title\n## Sub Section\n### Another Section";
    
        // Create a test file 
        $testfile = "{$this->markdownDir}/headers.md";
        File::put($testfile,$markdown);

        $response = $this->get('/page/headers');

        $response->assertStatus(200);
        $response->assertSee('id="main-title"', false);
        $response->assertSee('id="sub-section"', false);
        $response->assertSee('id="another-section"', false);

        File::delete($testfile);
    }

    public function test_show_handles_pages_without_table_of_contents(): void
    {
        $response = $this->get('/page/data-analysis-software');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertViewHas('table_of_contents', false);
        $response->assertViewHas('right_side_image', 'https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-image-2.jpeg');
    }
}
