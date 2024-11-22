<?php

namespace Tests\Unit\Exports;

use App\Exports\StatementExportTrait;
use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementExportTraitTest extends TestCase
{
    use StatementExportTrait;
    use RefreshDatabase;

    /** 
     * @test  
     */
    public function test_headings_returns_all_required_columns()
    {
        $headings = $this->headings();
        
        $this->assertIsArray($headings);
        $this->assertContains('uuid', $headings);
        $this->assertContains('decision_visibility', $headings);
        $this->assertContains('platform_name', $headings);
        $this->assertContains('created_at', $headings);
    }

    /** @test */
    public function test_headings_light_returns_subset_of_columns()
    {
        $headings = $this->headingsLight();
        
        $this->assertIsArray($headings);
        $this->assertContains('uuid', $headings);
        $this->assertContains('decision_visibility', $headings);
        // Light version should exclude certain fields
        $this->assertNotContains('illegal_content_explanation', $headings);
        $this->assertNotContains('incompatible_content_explanation', $headings);
    }

    
    /** @test */
    public function test_map_function_maps_statements_correctly()
    {
        $platform = Platform::factory()->create();
        $statement = Statement::factory()->create(['platform_id' => $platform->id]);
        
        $mapped = $this->map($statement);
        
        $this->assertIsArray($mapped);
        $this->assertTrue(in_array($statement->uuid, $mapped)); 
        $this->assertContains($platform->name, $mapped);
    }

    
    /** @test */
    public function test_mapRaw_function_maps_statements_correctly()
    {
        $platform = Platform::factory()->create();
        $statement = Statement::factory()->create(['platform_id' => $platform->id]);

        $platforms = Platform::all()->pluck('name', 'id')->toArray();
        
        $mapped = $this->mapRaw($statement, $platforms);
        
        $this->assertIsArray($mapped);
        $this->assertTrue(in_array($statement->uuid, $mapped)); 
        $this->assertContains($platform->name, $mapped);
    }
    
    /** @test */
    public function test_mapRawLight_function_maps_statements_correctly()
    {
        $platform = Platform::factory()->create();
        $statement = Statement::factory()->create(['platform_id' => $platform->id]);
        $platforms = Platform::all()->pluck('name', 'id')->toArray();

        $mapped = $this->mapRawLight($statement, $platforms);
        
        $this->assertIsArray($mapped);
        $this->assertTrue(in_array($statement->uuid, $mapped)); 
        $this->assertContains($platform->name, $mapped);
    }
}
