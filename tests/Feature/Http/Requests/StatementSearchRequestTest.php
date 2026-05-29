<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StatementSearchRequest;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StatementSearchRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('test-statement-search', function (StatementSearchRequest $request) {
            return response()->json($request->validated());
        })->middleware('web')->name('test-statement-search');
    }

    public function test_it_authorizes_search_requests(): void
    {
        $this->assertTrue((new StatementSearchRequest)->authorize());
    }

    public function test_it_accepts_a_valid_search_payload(): void
    {
        $language = array_key_first(app(EuropeanLanguagesService::class)->getAllLanguages());

        $response = $this->getSearchJson([
            's' => 'moderation',
            'platform_id' => [1],
            'decision_ground' => [array_key_first(Statement::DECISION_GROUNDS)],
            'content_language' => [$language],
            'created_at_start' => '2024-01-01',
            'created_at_end' => '2024-01-31',
        ]);

        $response->assertOk()
            ->assertJsonPath('s', 'moderation')
            ->assertJsonPath('platform_id.0', '1')
            ->assertJsonPath('content_language.0', $language);
    }

    public function test_it_rejects_invalid_scalar_array_and_date_values(): void
    {
        $response = $this->getSearchJson([
            's' => str_repeat('a', 256),
            'platform_id' => 'not-an-array',
            'created_at_start' => '2024-02-01',
            'created_at_end' => '2024-01-01',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                's',
                'platform_id',
                'created_at_end',
            ]);
    }

    public function test_it_rejects_non_integer_platform_ids(): void
    {
        $response = $this->getSearchJson([
            'platform_id' => ['not-an-integer'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['platform_id.0']);
    }

    public function test_it_filters_unsupported_option_values_before_validation(): void
    {
        $country = array_key_first(app(EuropeanCountriesService::class)->getOptionsArray());
        $language = array_key_first(app(EuropeanLanguagesService::class)->getAllLanguages());
        $decisionGround = array_key_first(Statement::DECISION_GROUNDS);
        $contentType = array_key_first(Statement::CONTENT_TYPES);

        $response = $this->getSearchJson([
            'decision_ground' => [$decisionGround, 'NOT_A_DECISION_GROUND'],
            'territorial_scope' => [$country, 'XX'],
            'content_language' => [$language, 'not-a-language'],
            'content_type' => [$contentType, 'NOT_A_CONTENT_TYPE'],
        ]);

        $response->assertOk()
            ->assertJsonPath('decision_ground', [$decisionGround])
            ->assertJsonPath('territorial_scope', [$country])
            ->assertJsonPath('content_language', [$language])
            ->assertJsonPath('content_type', [$contentType]);
    }

    private function getSearchJson(array $payload): TestResponse
    {
        return $this->getJson('/test-statement-search?'.http_build_query($payload));
    }
}
