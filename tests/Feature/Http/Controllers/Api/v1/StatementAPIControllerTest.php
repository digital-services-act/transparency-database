<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use App\Models\PlatformPuid;
use App\Models\Statement;
use App\Models\User;
use App\Services\StatementSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery\MockInterface;
use Tests\TestCase;

class StatementAPIControllerTest extends TestCase
{
    use AdditionalAssertions;
    use RefreshDatabase;
    use WithFaker;
    private array $required_fields;

    private Statement $statement;

    /**
     * @return array
     */
    public function createFullStatements($count = 5): array
    {
        $statements = Statement::factory()->count($count)->make()->toArray();

        foreach ($statements as &$statement) {
            $statement['puid'] = Str::uuid()->toString();
            $statement['content_type'] = $this->faker->randomElements(array_keys(Statement::CONTENT_TYPES), 2, false);
            unset($statement['permalink']);
            unset($statement['platform_name']);
            unset($statement['self']);
        }

        return $statements;
    }


    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->required_fields = [
            'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_DISABLED', 'DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED'],
            'decision_monetary' => null,
            'decision_provision' => null,
            'decision_account' => null,
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'puid' => 'TK421',
            'territorial_scope' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source_identity' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection' => 'No',
            'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY',
            'application_date' => '2023-05-18',
            'content_date' => '2023-05-18'
        ];
    }


    /**
     * @test
     */
    public function api_statement_show_works(): void
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();
        $attributes = $this->required_fields;
        $attributes['user_id'] = $admin->id;
        $attributes['platform_id'] = $admin->platform_id;
        $this->statement = Statement::create($attributes);

        $response = $this->get(route('api.v1.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($this->statement->decision_ground, $response->json('decision_ground'));
        $this->assertEquals($this->statement->uuid, $response->json('uuid'));
        $this->assertEquals($this->statement->source_identity, $response->json('source_identity'));
    }

    /**
     * @test
     */
    public function api_statement_existing_puid_works(): void
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();
        $attributes = $this->required_fields;
        $attributes['user_id'] = $admin->id;
        $attributes['platform_id'] = $admin->platform_id;
        $this->statement = Statement::create($attributes);

        $statement = $this->statement;

        $this->mock(StatementSearchService::class, static function (MockInterface $mock) use ($statement) {
            $mock->shouldReceive('PlatformIdPuidToId')->andReturn($statement->id);
        });

        $response = $this->get(route('api.v1.statement.existing-puid', [$this->statement->puid]), [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_FOUND);
        $this->assertEquals($this->statement->decision_ground, $response->json('decision_ground'));
        $this->assertEquals($this->statement->uuid, $response->json('uuid'));
        $this->assertEquals($this->statement->source_identity, $response->json('source_identity'));
    }

    /**
     * @test
     */
    public function api_statement_existing_puid_gives_404(): void
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();
        $attributes = $this->required_fields;
        $attributes['user_id'] = $admin->id;
        $attributes['platform_id'] = $admin->platform_id;
        $this->statement = Statement::create($attributes);

        $this->mock(StatementSearchService::class, static function (MockInterface $mock) {
            $mock->shouldReceive('PlatformIdPuidToId')->andReturn(0);
        });

        $response = $this->get(route('api.v1.statement.existing-puid', ['a-bad-puid']), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function api_statement_show_requires_auth(): void
    {
        $this->setUpFullySeededDatabase();
        $attributes = $this->required_fields;
        $attributes['user_id'] = User::all()->random()->first()->id;
        $attributes['platform_id'] = Platform::all()->random()->first()->id;
        $this->statement = Statement::create($attributes);
        $response = $this->get(route('api.v1.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_statement_store_requires_auth(): void
    {
        $this->setUpFullySeededDatabase();
        // Not signing in.
        $this->assertCount(10, Statement::all());
        $response = $this->post(route('api.v1.statement.store'), $this->required_fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function api_statement_store_works(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertNull($statement->account_type);
        $this->assertNull($statement->content_language);
    }



    /**
     * @return void
     * @test
     */
    public function api_statement_content_language_is_stored(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
            'account_type' => 'ACCOUNT_TYPE_BUSINESS',
            'content_language' => 'EN'
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertNotNull($statement->account_type);
        $this->assertEquals('ACCOUNT_TYPE_BUSINESS', $statement->account_type);
        $this->assertNotNull($statement->content_type);
        $this->assertEquals('EN', $statement->content_language);
    }

    /**
     * @return void
     * @test
     */
    public function api_statement_content_language_can_be_non_european(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
            'account_type' => 'ACCOUNT_TYPE_BUSINESS',
            'content_language' => 'VI'
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertNotNull($statement->account_type);
        $this->assertEquals('ACCOUNT_TYPE_BUSINESS', $statement->account_type);
        $this->assertNotNull($statement->content_type);
        $this->assertEquals('VI', $statement->content_language);
    }

    /**
     * @return void
     * @test
     */
    public function api_statement_content_language_must_be_valid(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',

            'account_type' => 'ACCOUNT_TYPE_BUSINESS',
            'content_language' => 'XX'
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(10, Statement::all());
    }

    /**
     * @return void
     * @test
     */
    public function api_statement_content_language_must_be_uppercase(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',

            'account_type' => 'ACCOUNT_TYPE_BUSINESS',
            'content_language' => 'en'
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(10, Statement::all());
    }

    /**
     * @return void
     * @test
     */
    public function api_statement_account_type_is_stored(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',

            'account_type' => 'ACCOUNT_TYPE_BUSINESS'
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);

        $this->assertNotNull($statement->account_type);
        $this->assertEquals('ACCOUNT_TYPE_BUSINESS', $statement->account_type);
    }

    /**
     * @return void
     * @test
     */
    public function api_statement_account_type_is_validated(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20-05',

            'account_type' => 'ACCOUNT_TYPE_NOT_VALID'
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(10, Statement::all());
    }

    /**
     * @test
     */
    public function api_statement_json_store_works(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-07-15',

        ]);
        $object = new \stdClass();
        foreach ($fields as $key => $value) {
            $object->$key = $value;
        }

        $json = json_encode($object);
        $response = $this->call(
            'POST',
            route('api.v1.statement.store'),
            [],
            [],
            [],
            $headers = [
                'HTTP_CONTENT_LENGTH' => mb_strlen($json, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            $json
        );

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);

        $this->assertInstanceOf(Carbon::class, $statement->application_date);

        $this->assertNull($statement->decision_ground_reference_url);
    }


    /**
     * @test
     */
    public function application_date_must_be_correct_format(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $date = Carbon::createFromDate(2023, 2, 5,);

        $this->assertCount(10, Statement::all());

        $application_date_in = date('Y-m-d');
        $end_date_in = date('Y-m-d', time() + (7 * 24 * 60 * 60));

        $fields = array_merge($this->required_fields, [
            'application_date' => $application_date_in,
            'end_date_monetary_restriction' => $end_date_in
        ]);
        $object = new \stdClass();
        foreach ($fields as $key => $value) {
            $object->$key = $value;
        }

        $json = json_encode($object);
        $response = $this->call(
            'POST',
            route('api.v1.statement.store'),
            [],
            [],
            [],
            $headers = [
                'HTTP_CONTENT_LENGTH' => mb_strlen($json, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            $json
        );

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);

        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_monetary_restriction);

        $resource = $statement->toArray();
        $this->assertEquals($application_date_in, $resource['application_date']);
    }

    /**
     * @test
     */
    public function request_rejects_bad_dates(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $application_date_in = '2023-4-4-4';

        $fields = array_merge($this->required_fields, [
            'application_date' => $application_date_in,
        ]);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The application date does not match the format YYYY-MM-DD.', $response->json('message'));
    }

    /**
     * @test
     */
    public function api_statement_store_rejects_bad_decision_ground_urls(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'decision_ground_reference_url' => 'notvalidurl',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(10, Statement::all());
    }

    /**
     * @test
     */
    public function api_statement_store_accepts_google_decision_ground_urls(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'decision_ground_reference_url' => 'https://www.goodurl.com',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
    }

    /**
     * @test
     */
    public function request_rejects_bad_countries(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'territorial_scope' => ['XY', 'ZZ'],
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The selected territorial scope is invalid.', $response->json('message'));
    }

    /**
     * @test
     */
    public function store_does_not_save_optional_fields_non_related_to_illegal_content(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNull($statement->incompatible_content_ground);
        $this->assertNull($statement->incompatible_content_explanation);
    }


    /**
     * @test
     */
    public function store_does_not_save_optional_fields_non_related_to_incompatible_content(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'decision_ground' => 'DECISION_GROUND_INCOMPATIBLE_CONTENT',
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
            'incompatible_content_illegal' => 'No',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNull($statement->illegal_content_legal_ground);
        $this->assertNull($statement->illegal_content_explanation);
    }

    /**
     * @test
     */
    public function request_rejects_bad_puids(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $application_puid_in = 'very bad é + pu.id';

        $fields = array_merge($this->required_fields, [
            'puid' => $application_puid_in,
        ]);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The puid format is invalid.', $response->json('message'));
    }

    /**
     * @test
     */
    public function store_enforces_puid_uniqueness(): void
    {

        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'puid' => ''
        ]);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $json = $response->json();
        $this->assertNotNull($json['errors']);
        $this->assertNotNull($json['errors']['puid']);
        $this->assertEquals('The puid field is required.', $json['errors']['puid'][0]);
        $this->assertDatabaseCount(PlatformPuid::class,0);

        $fields = array_merge($this->required_fields, [
            'puid' => 'new-puid-123'
        ]);


        // Now let's create one
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(PlatformPuid::class,1);
        $count_before = Statement::all()->count();

        // Let's do it again
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('puid');
        $this->assertDatabaseCount(PlatformPuid::class,1);
        $this->assertArrayHasKey('existing', $response->json());
        $this->assertArrayHasKey('puid', $response->json('existing'));

        $count_after = Statement::all()->count();

        $this->assertEquals($count_after, $count_before);
    }

    /**
     * @test
     */
    public function store_should_refresh_the_cache_when_cache_expired_and_archived_statement_is_present(): void
    {

        $user = $this->signInAsAdmin();
        $this->assertDatabaseCount(PlatformPuid::class,0);
        $this->withoutExceptionHandling();

        $puid = 'new-puid-456';

        PlatformPuid::create([
            'puid' => $puid,
            'platform_id' => $user->platform->id
        ]);

        $this->assertDatabaseCount(PlatformPuid::class,1);

        $fields = array_merge($this->required_fields, [
            'puid' => $puid
        ]);

        $key = sprintf('puid-%s-%s', $user->platform->id, $puid);
        $this->assertFalse(Cache::has($key));


        // Now let's create one
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);


        $this->assertDatabaseCount(PlatformPuid::class,1);
        $this->assertTrue(Cache::has($key));
    }



    /**
     * @return void
     * @test
     */
    public function on_store_puid_is_shown_but_not_on_show(): void
    {
        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();

        $object = new \stdClass();
        foreach ($this->required_fields as $key => $value) {
            $object->$key = $value;
        }

        $json = json_encode($object);
        $response = $this->call(
            'POST',
            route('api.v1.statement.store'),
            [],
            [],
            [],
            $headers = [
                'HTTP_CONTENT_LENGTH' => mb_strlen($json, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            $json
        );

        $response->assertStatus(Response::HTTP_CREATED);
        // It shows on the store response
        $this->assertNotNull($response->json('puid'));
        $content = $response->content();
        $this->assertStringContainsString('"puid":', $content);


        // In the show call it should be not there or null
        $response = $this->call('GET', route('api.v1.statement.show', ['statement' => $response->json('id')]));
        $this->assertNull($response->json('puid'));
        $content = $response->content();
        $this->assertStringNotContainsString('"puid":', $content);

    }

    /**
     * @test
     */
    public function store_should_save_content_type_other(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'content_type' => ['CONTENT_TYPE_APP', 'CONTENT_TYPE_OTHER'],
            'content_type_other' => 'foobar other',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->content_type);
        $this->assertNotNull($statement->content_type_other);
    }

    /**
     * @test
     */
    public function store_should_not_save_content_type_other(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'content_type' => ['CONTENT_TYPE_AUDIO', 'CONTENT_TYPE_APP', 'CONTENT_TYPE_VIDEO'],
            'content_type_other' => 'foobar other',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->content_type);
        $this->assertNull($statement->content_type_other);
    }


    /**
     * @test
     */
    public function store_should_save_source_identity(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'source_type' => 'SOURCE_TYPE_OTHER_NOTIFICATION',
            'source_identity' => 'foobar other',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->source_type);
        $this->assertNotNull($statement->source_identity);
    }

    /**
     * @test
     */
    public function store_should_not_save_source_identity(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'source_type' => 'SOURCE_VOLUNTARY',
            'source_identity' => 'foobar other',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->source_type);
        $this->assertNull($statement->source_identity);
    }



    /**
     * @test
     */
    public function store_should_save_end_dates(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'end_date_visibility_restriction' => '2023-08-10',
            'end_date_monetary_restriction' => '2023-08-11',
            'end_date_service_restriction' => '2023-08-12',
            'end_date_account_restriction' => '2023-08-13',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertInstanceOf(Carbon::class, $statement->end_date_visibility_restriction);
        $this->assertEquals('2023-08-10 00:00:00', (string)$statement->end_date_visibility_restriction);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_monetary_restriction);
        $this->assertEquals('2023-08-11 00:00:00', (string)$statement->end_date_monetary_restriction);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_service_restriction);
        $this->assertEquals('2023-08-12 00:00:00', (string)$statement->end_date_service_restriction);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_account_restriction);
        $this->assertEquals('2023-08-13 00:00:00', (string)$statement->end_date_account_restriction);
    }

    /**
     * @test
     */
    public function store_should_save_keywords_with_other(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'category_specification' => ['KEYWORD_ADULT_SEXUAL_MATERIAL', 'KEYWORD_DESIGN_INFRINGEMENT', 'KEYWORD_OTHER'],
            'category_specification_other' => 'foobar keyword',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->category_specification);
        $this->assertNotNull($statement->category_specification_other);

    }

    /**
     * @test
     */
    public function store_should_save_not_duplicate_categories(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'category' => 'STATEMENT_CATEGORY_VIOLENCE',
            'category_addition' => ['STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH', 'STATEMENT_CATEGORY_VIOLENCE'],
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement->category);
        $this->assertNotNull($statement->category_addition);
        $this->assertCount(1, $statement->category_addition);

    }

    /**
     * @test
     */
    public function store_should_save_empty_additional_categories_as_empty_array(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'category' => 'STATEMENT_CATEGORY_VIOLENCE',
            'category_addition' => [],
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertEquals([], $statement->category_addition);

    }

    /**
     * @test
     */
    public function store_should_save_null_decisions_when_account_is_suspended(): void
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'decision_visibility' => null,
            'decision_monetary' => null,
            'decision_provision' => null,
            'decision_account' => 'DECISION_ACCOUNT_SUSPENDED',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);

        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertEquals([], $statement->decision_visibility);
        $this->assertNull($statement->decision_monetary);
        $this->assertNull($statement->decision_provision);

    }




}

