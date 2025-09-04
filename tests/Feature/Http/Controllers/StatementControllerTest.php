<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
// use JMac\Testing\Traits\AdditionalAssertions;
use Tests\Feature\Http\Controllers\Api\v1\StatementAPIControllerTest;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\StatementController
 */
class StatementControllerTest extends TestCase
{
    // use AdditionalAssertions;
    use RefreshDatabase;
    use WithFaker;

    protected $dummy_attributes = [
        'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_DISABLED', 'DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED'],
        'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
        'content_type' => ['CONTENT_TYPE_VIDEO'],
        'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
        'illegal_content_legal_ground' => 'foo',
        'illegal_content_explanation' => 'bar',
        'territorial_scope' => ['BE', 'FR'],
        'url' => 'https://www.test.com',
        'puid' => 'THX1138',
        'content_date' => '2023-05-12',
        'application_date' => '2023-05-12',
        'source_type' => 'SOURCE_ARTICLE_16',
        'decision_facts' => 'Facts and circumstances',
        'automated_detection' => 'Yes',
        'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY',
    ];

    //    /**
    //     * @test
    //     */
    //    public function index_displays_error_if_not_logged()
    //    {
    //
    //        $response = $this->get(route('statement.index'));
    //        $response->assertOk();
    //        $response->assertViewIs('statement.index');
    //        $response->assertViewHas('statements');
    //    }

    /**
     * @test
     */
    public function index_displays_view_if_logged_with_rights(): void
    {
        $this->signInAsAdmin();
        $response = $this->get(route('statement.index'));
        $response->assertOk();
        $response->assertViewIs('statement.index');
        $response->assertViewHas('statements');
    }

    /**
     * @test
     */
    public function index_handles_the_max_pages_issue(): void
    {
        $this->signInAsAdmin();
        $response = $this->get(route('statement.index', ['page' => 400]));
        $response->assertOk();
        $response->assertViewIs('statement.index');
        $response->assertViewHas('statements');
    }

    /**
     * @test
     */
    public function basic_search_route_works(): void
    {
        $this->signInAsAdmin();
        $response = $this->get(route('statement.search'));
        $response->assertOk();
        $response->assertViewIs('statement.search');
    }

    /**
     * @test
     */
    public function create_blocks_when_no_platform(): void
    {
        $user = $this->signInAsAdmin();
        $user->platform_id = null;
        $user->save();

        $response = $this->get(route('statement.create'));
        $response->assertRedirect();
    }

    /**
     * @test
     */
    public function show_throws_404_if_not_found(): void
    {
        $this->signInAsAdmin();
        $statement = Statement::factory()->create();
        $response = $this->get(route('statement.show', ['statement' => 0]));
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function it_can_show_the_index(): void
    {
        $response = $this->get(route('statement.index'));
        $response->assertOk();
    }

    /**
     * @test
     */
    public function export_downloads_a_file(): void
    {

        $this->signInAsAdmin();
        $response = $this->get(route('statement.export'));
        $response->assertOk();
        $response->assertDownload();
    }

    /**
     * @test
     */
    public function create_displays_view(): void
    {

        $user = $this->signInAsAdmin();

        $response = $this->get(route('statement.create'));
        $response->assertOk();
        $response->assertViewIs('statement.create');
    }

    /**
     * @test
     */
    public function create_must_be_authenticated(): void
    {

        // The cas is set to masquerade in testing mode.
        // So when we make a call to a cas middleware route we get logged in.
        // Thus before we make this call we are nobody
        $u = auth()->user();
        $this->assertNull($u);

        $response = $this->get(route('statement.create'));
        $response->assertRedirectContains('/login');

    }

    /**
     * @test
     */
    public function show_displays_view(): void
    {

        $this->signInAsAdmin();

        $statement = Statement::factory()->create();
        $response = $this->get(route('statement.show', $statement));

        $response->assertOk();
        $response->assertViewIs('statement.show');
        $response->assertViewHas('statement');
    }

    /**
     * @test
     */
    public function show_beta_throws_404_if_not_found(): void
    {
        $this->signInAsAdmin();

        $response = $this->get(route('statement.show', ['statement' => 100000000005]));
        $response->assertNotFound();
    }

    /**
     * @test
     *
     * @see StatementAPIControllerTest
     */
    public function store_saves_and_redirects(): void
    {

        // This is a basic test that the normal controller is working.
        // For more advanced testing on the request and such, see the API testing.
        $user = $this->signInAsAdmin();

        // 10 from seeding.
        $this->assertCount(10, Statement::all());

        // When making statements via the FORM
        // The dates come in as d-m-Y from the ECL datepicker.
        $response = $this->post(route('statement.store'), $this->dummy_attributes);

        $this->assertCount(11, Statement::all());
        $statement = Statement::orderBy('id', 'DESC')->first();
        $this->assertNotNull($statement);
        $this->assertEquals(Statement::METHOD_FORM, $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertEquals('2023-05-12 00:00:00', (string) $statement->application_date);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertEquals('2023-05-12 00:00:00', (string) $statement->content_date);
        $this->assertInstanceOf(Carbon::class, $statement->content_date);

        $response->assertRedirect(route('statement.show', $statement->uuid));
    }

    /**
     * @test
     *
     * @see StatementAPIControllerTest
     */
    public function store_fails_with_existing_puid(): void
    {

        // This is a basic test that the normal controller is working.
        // For more advanced testing on the request and such, see the API testing.
        $user = $this->signInAsAdmin();

        // 10 from seeding.
        $this->assertCount(10, Statement::all());

        // When making statements via the FORM
        // The dates come in as d-m-Y from the ECL datepicker.
        $response = $this->post(route('statement.store'), $this->dummy_attributes);

        $this->assertCount(11, Statement::all());

        $response = $this->post(route('statement.store'), $this->dummy_attributes);

        $response->assertRedirect(route('statement.index'));
        $response->assertSessionHas('error', 'The PUID is not unique in the database');
    }

    /**
     * @test
     */
    public function index_uses_elasticsearch_when_configured(): void
    {
        $this->signInAsAdmin();

        // Mock the elastic search service
        $mock = $this->mock(\App\Services\StatementElasticSearchService::class);
        $mock->shouldReceive('query')->once()->andReturn([
            'statements' => Statement::query(),
            'total' => 0,
        ]);

        // Set the config to use elasticsearch
        config()->set('elasticsearch.uri', ['http://localhost:9200']);

        $response = $this->get(route('statement.index'));
        $response->assertOk();
    }

    /**
     * @test
     */
    public function index_uses_database_when_elasticsearch_is_not_configured(): void
    {
        $this->signInAsAdmin();

        // Mock the statement query service
        $mock = $this->mock(\App\Services\StatementQueryService::class);
        $mock->shouldReceive('query')->twice()->andReturn(Statement::query());

        // Ensure elasticsearch is not configured
        config()->set('elasticsearch.uri', [null]);

        $response = $this->get(route('statement.index'));
        $response->assertOk();
    }
}
