<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

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
        $this->fakeHomeStats();

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
        $this->fakeHomeStats();

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
        $this->fakeHomeStats();

        // Clear any existing platforms
        Platform::query()->delete();

        // Act
        $response = $this->get('/');

        // Assert - Even with no platforms, minimum should be 1
        $viewData = $response->viewData('platforms_total');
        $this->assertEquals(1, $viewData);
    }

    public function test_index_uses_database_stats_when_elasticsearch_is_not_configured(): void
    {
        config([
            'elasticsearch.enabled' => false,
            'elasticsearch.uri' => [],
        ]);

        Statement::query()->delete();
        Statement::factory()->count(3)->create([
            'category' => 'STATEMENT_CATEGORY_VIOLENCE',
            'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_REMOVED'],
            'automated_decision' => 'AUTOMATED_DECISION_FULLY',
        ]);
        Statement::factory()->count(1)->create([
            'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_DISABLED'],
            'automated_decision' => 'AUTOMATED_DECISION_NOT_AUTOMATED',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertEquals(4, $response->viewData('total'));
        $this->assertEquals(75, $response->viewData('automated_decision_percentage'));

        $topCategories = $response->viewData('top_categories');
        $this->assertEquals('STATEMENT_CATEGORY_VIOLENCE', $topCategories[0]['value']);
        $this->assertEquals(3, $topCategories[0]['total']);

        $topDecisionVisibilities = $response->viewData('top_decisions_visibility');
        $this->assertEquals('DECISION_VISIBILITY_CONTENT_REMOVED', $topDecisionVisibilities[0]['value']);
        $this->assertEquals(3, $topDecisionVisibilities[0]['total']);
    }

    private function fakeHomeStats(): void
    {
        $elastic = ElasticMocker::fake()->sqlCountReturns(100);

        foreach (array_keys(Statement::STATEMENT_CATEGORIES) as $category) {
            $elastic->sqlCountReturns(match ($category) {
                'STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH' => 50,
                'STATEMENT_CATEGORY_VIOLENCE' => 30,
                'STATEMENT_CATEGORY_ANIMAL_WELFARE' => 20,
                'STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS' => 10,
                default => 1,
            });
        }

        foreach (array_keys(Statement::DECISION_VISIBILITIES) as $decisionVisibility) {
            $elastic->sqlCountReturns(match ($decisionVisibility) {
                'DECISION_VISIBILITY_CONTENT_REMOVED' => 40,
                'DECISION_VISIBILITY_CONTENT_DISABLED' => 30,
                'DECISION_VISIBILITY_CONTENT_DEMOTED' => 20,
                'DECISION_VISIBILITY_CONTENT_LABELLED' => 10,
                default => 1,
            });
        }

        $elastic->sqlCountReturns(75);
    }
}
