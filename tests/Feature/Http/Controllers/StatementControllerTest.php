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
        'decision_taken' => 'DECISION_ALL',
        'decision_ground' => 'ILLEGAL_CONTENT',
        'category' => 'FRAUD',
        'platform_type' => 'SOCIAL_MEDIA',
        'illegal_content_legal_ground' => 'foo',
        'illegal_content_explanation' => 'bar',
        'countries_list' => ['BE','FR'],
        'date_abolished' => '03-01-2023',
        'source' => 'SOURCE_ARTICLE_16',
        'source_identity' => 'notifier',
        'automated_detection' => 'Yes',
        'automated_decision' => 'Yes',
        'automated_takedown' => 'Yes'
    ];

    /**
     * @test
     */
    public function index_displays_view()
    {
        $this->seed();
        $statements = Statement::factory()->count(3)->create();
        $response = $this->get(route('statement.index'));
        $response->assertOk();
        $response->assertViewIs('statement.index');
        $response->assertViewHas('statements');
    }

    /**
     * @test
     */
    public function index_does_not_auth()
    {
        // The cas is set to masquerade in testing mode.
        // So when we make a call to a cas middleware route we get logged in.
        // If we make a call to a non cas route nothing should happen.
        $u = auth()->user();
        $this->assertNull($u);
        $response = $this->get(route('statement.index'));
        $u = auth()->user();
        $this->assertNull($u);
    }


    /**
     * @test
     */
    public function create_displays_view()
    {
        $this->seed();
        /** @var User $user */
        $user = $this->signIn();
        PermissionsSeeder::resetRolesAndPermissions();
        $user->assignRole('Admin');

        $response = $this->get(route('statement.create'));
        $response->assertOk();
        $response->assertViewIs('statement.create');
    }

    /**
     * @test
     */
    public function create_must_be_authenticated()
    {
        $this->seed();
        // The cas is set to masquerade in testing mode.
        // So when we make a call to a cas middleware route we get logged in.
        // Thus before we make this call we are nobody
        $u = auth()->user();
        $this->assertNull($u);

        $response = $this->get(route('statement.create'));
        $response->assertStatus(Response::HTTP_FORBIDDEN);

        // After we made this call we are somebody
        $u = auth()->user();
        $this->assertNotNull($u);
    }


    /**
     * @test
     */
    public function show_displays_view()
    {
        $this->seed();
        $statement = Statement::factory()->create();
        $user = $this->signIn();
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
        $this->seed();
        // This is a basic test that the normal controller is working.
        // For more advanced testing on the request and such, see the API testing.
        PermissionsSeeder::resetRolesAndPermissions();
        /** @var User $user */
        $user = $this->signIn();
        $user->assignRole('Admin');

        // 200 from seeding.
        $this->assertCount(200, Statement::all());

        // When making statements via the FORM
        // The dates come in as d-m-Y from the ECL datepicker.
        $response = $this->post(route('statement.store'), $this->dummy_attributes);

        $this->assertCount(201, Statement::all());
        $statement = Statement::latest()->first();
        $this->assertNotNull($statement);
        $this->assertEquals('FORM', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertEquals('2023-01-03 00:00:00', $statement->date_abolished);
        $this->assertInstanceOf(Carbon::class, $statement->date_abolished);

        $response->assertRedirect(route('statement.index'));
    }
}
