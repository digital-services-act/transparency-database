<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Statement;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\Feature\Http\Controllers\Api\v1\StatementAPIControllerTest;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\StatementController
 */
class StatementControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    protected $dummy_attributes = [
        'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_DISABLED','DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED'],
        'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
        'content_type' => ['CONTENT_TYPE_VIDEO'],
        'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
        'illegal_content_legal_ground' => 'foo',
        'illegal_content_explanation' => 'bar',
        'territorial_scope' => ['BE','FR'],
        'url' => 'https://www.test.com',
        'puid' => 'THX1138',
        'content_date' => '2023-05-12',
        'application_date' => '2023-05-12',
        'source_type' => 'SOURCE_ARTICLE_16',
        'decision_facts' => 'Facts and circumstances',
        'automated_detection' => 'Yes',
        'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY'
    ];

//    /**
//     * @test
//     */
//    public function index_displays_error_if_not_logged()
//    {
//        $this->setUpFullySeededDatabase();
//        $response = $this->get(route('statement.index'));
//        $response->assertOk();
//        $response->assertViewIs('statement.index');
//        $response->assertViewHas('statements');
//    }

    /**
     * @test
     */
    public function index_displays_view_if_logged_with_rights()
    {
        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();
        $response = $this->get(route('statement.index'));
        $response->assertOk();
        $response->assertViewIs('statement.index');
        $response->assertViewHas('statements');
    }

    //Removed as index does need auth now
//    /**
//     * @test
//     */
//    public function index_does_not_auth()
//    {
//        // The cas is set to masquerade in testing mode.
//        // So when we make a call to a cas middleware route we get logged in.
//        // If we make a call to a non cas route nothing should happen.
//        $u = auth()->user();
//        $this->assertNull($u);
//        $response = $this->get(route('statement.index'));
//        $u = auth()->user();
//        $this->assertNotNull($u);
//    }

    /**
     * @test
     */
    public function export_downloads_a_file()
    {
        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();
        $response = $this->get(route('statement.export'));
        $response->assertOk();
        $response->assertDownload();
    }


    /**
     * @test
     */
    public function create_displays_view()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $response = $this->get(route('statement.create'));
        $response->assertOk();
        $response->assertViewIs('statement.create');
    }

    /**
     * @test
     */
    public function create_must_be_authenticated()
    {
        $this->setUpFullySeededDatabase();
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
    public function show_displays_view()
    {
        $this->setUpFullySeededDatabase();
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
    public function store_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\StatementController::class,
            'store',
            \App\Http\Requests\StatementStoreRequest::class
        );
    }

    /**
     * @test
     * @see StatementAPIControllerTest
     */
    public function store_saves_and_redirects()
    {
        $this->setUpFullySeededDatabase();
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
        $this->assertEquals('2023-05-12 00:00:00', (string)$statement->application_date);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertEquals('2023-05-12 00:00:00', (string)$statement->content_date);
        $this->assertInstanceOf(Carbon::class, $statement->content_date);

        $response->assertRedirect(route('statement.index'));
    }
}
