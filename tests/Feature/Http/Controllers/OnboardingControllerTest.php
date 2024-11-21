<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_requires_authentication()
    {
        $response = $this->get(route('onboarding.index'));
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function index_requires_onboarding_role()
    {
        $this->signIn();
        $response = $this->get(route('onboarding.index'));
        $response->assertForbidden();
    }

    /** @test */
    public function onboarding_user_can_access_index()
    {
        $this->signInAsOnboarding();
        $response = $this->get(route('onboarding.index'));
        $response->assertOk();
        $response->assertViewIs('onboarding.index');
        $response->assertViewHas(['platforms', 'options', 'all_platforms_count', 'platform_ids_methods_data']);
    }

    /** @test */
    public function index_displays_correct_platform_count()
    {
        $this->signInAsOnboarding();
        $initialCount = Platform::nonDSA()->count();
        Platform::factory()->count(3)->create();
        
        $response = $this->get(route('onboarding.index'));
        $response->assertViewHas('all_platforms_count', $initialCount + 3);
    }

    /** @test */
    public function can_filter_platforms_by_vlop_status()
    {
        $this->signInAsOnboarding();
        // Clear existing non-DSA platforms
        Platform::where('name', '!=', Platform::LABEL_DSA_TEAM)->delete();
        
        $vlopPlatform = Platform::factory()->create(['vlop' => true]);
        $nonVlopPlatform = Platform::factory()->create(['vlop' => false]);

        $response = $this->get(route('onboarding.index', ['vlop' => 1]));
        $platforms = $response->viewData('platforms');
        
        $this->assertTrue($platforms->contains($vlopPlatform));
        $this->assertFalse($platforms->contains($nonVlopPlatform));
    }

    /** @test */
    public function can_sort_platforms_by_name()
    {
        $this->signInAsOnboarding();
        // Clear existing non-DSA platforms
        Platform::where('name', '!=', Platform::LABEL_DSA_TEAM)->delete();
        
        $platformB = Platform::factory()->create(['name' => 'B Platform']);
        $platformA = Platform::factory()->create(['name' => 'A Platform']);
        $platformC = Platform::factory()->create(['name' => 'C Platform']);

        $response = $this->get(route('onboarding.index', ['sorting' => 'name:asc']));
        $platforms = $response->viewData('platforms');
        
        $this->assertEquals('A Platform', $platforms->first()->name);
        $this->assertEquals('C Platform', $platforms->last()->name);
    }

    /** @test */
    public function can_sort_platforms_by_creation_date()
    {
        $this->signInAsOnboarding();
        // Clear existing non-DSA platforms
        Platform::where('name', '!=', Platform::LABEL_DSA_TEAM)->delete();
        
        $oldPlatform = Platform::factory()->create(['created_at' => now()->subDays(2)]);
        $newPlatform = Platform::factory()->create(['created_at' => now()]);

        $response = $this->get(route('onboarding.index', ['sorting' => 'created_at:desc']));
        $platforms = $response->viewData('platforms');
        
        $this->assertTrue($platforms->first()->is($newPlatform));
        $this->assertTrue($platforms->last()->is($oldPlatform));
    }

    /** @test */
    public function can_search_platforms_by_name()
    {
        $this->signInAsOnboarding();
        $matchingPlatform = Platform::factory()->create(['name' => 'Test Platform']);
        $nonMatchingPlatform = Platform::factory()->create(['name' => 'Other Platform']);

        $response = $this->get(route('onboarding.index', ['s' => 'Test']));
        $platforms = $response->viewData('platforms');
        
        $this->assertTrue($platforms->contains($matchingPlatform));
        $this->assertFalse($platforms->contains($nonMatchingPlatform));
    }

    /** @test */
    public function invalid_sorting_parameters_default_to_name_asc()
    {
        $this->signInAsOnboarding();
        // Clear existing non-DSA platforms
        Platform::where('name', '!=', Platform::LABEL_DSA_TEAM)->delete();
        
        $platformB = Platform::factory()->create(['name' => 'B Platform']);
        $platformA = Platform::factory()->create(['name' => 'A Platform']);

        $response = $this->get(route('onboarding.index', ['sorting' => 'invalid:invalid']));
        $platforms = $response->viewData('platforms');
        
        $this->assertEquals('A Platform', $platforms->first()->name);
        $this->assertEquals('B Platform', $platforms->last()->name);
    }

    /** @test */
    public function options_contains_all_required_filter_choices()
    {
        $this->signInAsOnboarding();
        $response = $this->get(route('onboarding.index'));
        
        $options = $response->viewData('options');
        
        $this->assertArrayHasKey('vlops', $options);
        $this->assertArrayHasKey('onboardeds', $options);
        $this->assertArrayHasKey('has_tokens', $options);
        $this->assertArrayHasKey('has_statements', $options);
        $this->assertArrayHasKey('sorting', $options);
    }
}
