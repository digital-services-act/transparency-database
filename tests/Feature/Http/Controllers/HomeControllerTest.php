<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use App\Services\StatementSearchService;
use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_index_displays_view(): void
    {
        // Arrange
        $mockSearchService = $this->mock(StatementSearchService::class);
        $mockSearchService->shouldReceive('grandTotal')->once()->andReturn(100);
        $mockSearchService->shouldReceive('topCategories')->once()->andReturn([
            ['value' => 'STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH', 'count' => 50],
            ['value' => 'STATEMENT_CATEGORY_VIOLENCE', 'count' => 30],
            ['value' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE', 'count' => 20],
            ['value' => 'STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS', 'count' => 10]
        ]);
        $mockSearchService->shouldReceive('topDecisionVisibilities')->once()->andReturn([
            ['value' => 'DECISION_VISIBILITY_CONTENT_REMOVED', 'count' => 40],
            ['value' => 'DECISION_VISIBILITY_CONTENT_DISABLED', 'count' => 30],
            ['value' => 'DECISION_VISIBILITY_CONTENT_DEMOTED', 'count' => 20],
            ['value' => 'DECISION_VISIBILITY_CONTENT_LABELLED', 'count' => 10]
        ]);
        $mockSearchService->shouldReceive('fullyAutomatedDecisionPercentage')->once()->andReturn(75);

        // Create test platforms and clear any existing ones
        Platform::query()->delete();
        Platform::factory()->count(3)->create(['name' => 'Regular Platform']);
        Platform::factory()->create(['name' => Platform::LABEL_DSA_TEAM]); // This one shouldn't be counted

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('home');
        
        $viewData = $response->viewData('total');
        $this->assertEquals(100, $viewData);
        
        $viewData = $response->viewData('platforms_total');
        $this->assertEquals(3, $viewData);
        
        // Check only top 3 categories are passed
        $viewData = $response->viewData('top_categories');
        $this->assertCount(3, $viewData);
        $this->assertEquals('STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH', $viewData[0]['value']);
        $this->assertEquals('STATEMENT_CATEGORY_VIOLENCE', $viewData[1]['value']);
        $this->assertEquals('STATEMENT_CATEGORY_ANIMAL_WELFARE', $viewData[2]['value']);

        // Check only top 3 decision visibilities are passed
        $viewData = $response->viewData('top_decisions_visibility');
        $this->assertCount(3, $viewData);
        $this->assertEquals('DECISION_VISIBILITY_CONTENT_REMOVED', $viewData[0]['value']);
        $this->assertEquals('DECISION_VISIBILITY_CONTENT_DISABLED', $viewData[1]['value']);
        $this->assertEquals('DECISION_VISIBILITY_CONTENT_DEMOTED', $viewData[2]['value']);

        $viewData = $response->viewData('automated_decision_percentage');
        $this->assertEquals(75, $viewData);
    }

    public function test_platforms_total_is_cached(): void
    {
        // Arrange
        $mockSearchService = $this->mock(StatementSearchService::class);
        $mockSearchService->shouldReceive('grandTotal')->andReturn(100);
        $mockSearchService->shouldReceive('topCategories')->andReturn([]);
        $mockSearchService->shouldReceive('topDecisionVisibilities')->andReturn([]);
        $mockSearchService->shouldReceive('fullyAutomatedDecisionPercentage')->andReturn(0);

        // Create test platforms and clear any existing ones
        Platform::query()->delete();
        Platform::factory()->count(3)->create(['name' => 'Regular Platform']);

        // Act - First request
        $this->get('/');
        $cachedTotal = Cache::get('platforms_total');
        $this->assertEquals(3, $cachedTotal);

        // Create more platforms - these shouldn't affect the cached value
        Platform::factory()->count(2)->create(['name' => 'Regular Platform']);

        // Act - Second request
        $response = $this->get('/');
        
        // Assert - Should still show 3 from cache
        $viewData = $response->viewData('platforms_total');
        $this->assertEquals(3, $viewData);
    }

    public function test_minimum_platform_total_is_one(): void
    {
        // Arrange
        $mockSearchService = $this->mock(StatementSearchService::class);
        $mockSearchService->shouldReceive('grandTotal')->andReturn(100);
        $mockSearchService->shouldReceive('topCategories')->andReturn([]);
        $mockSearchService->shouldReceive('topDecisionVisibilities')->andReturn([]);
        $mockSearchService->shouldReceive('fullyAutomatedDecisionPercentage')->andReturn(0);

        // Clear any existing platforms
        Platform::query()->delete();
        
        // Act
        $response = $this->get('/');
        
        // Assert - Even with no platforms, minimum should be 1
        $viewData = $response->viewData('platforms_total');
        $this->assertEquals(1, $viewData);
    }
}
