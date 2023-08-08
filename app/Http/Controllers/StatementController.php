<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatementStoreRequest;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\DriveInService;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use App\Services\StatementSearchService;
use App\Services\StatementQueryService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use stdClass;


class StatementController extends Controller
{
    protected StatementQueryService $statement_query_service;
    protected StatementSearchService $statement_search_service;
    protected EuropeanCountriesService $european_countries_service;
    protected EuropeanLanguagesService $european_languages_service;
    protected DriveInService $drive_in_service;

    public function __construct(
        StatementQueryService $statement_query_service,
        StatementSearchService $statement_search_service,
        EuropeanCountriesService $european_countries_service,
        EuropeanLanguagesService $european_languages_service,
        DriveInService $drive_in_service
    )
    {
        $this->statement_query_service = $statement_query_service;
        $this->statement_search_service = $statement_search_service;
        $this->european_countries_service = $european_countries_service;
        $this->european_languages_service = $european_languages_service;
        $this->drive_in_service = $drive_in_service;
    }

    /**
     * @param Request $request
     *
     * @return View|Factory|Application
     */
    public function index(Request $request): View|Factory|Application
    {
        if (config('scout.driver') == 'opensearch') {
            $statements = $this->statement_search_service->query($request->query());
        } else {
            $statements = $this->statement_query_service->query($request->query());
        }

        $options = $this->prepareOptions();
        $statements = $statements->orderBy('created_at', 'DESC')->paginate(50)->withQueryString()->appends('query', null);
        $total = $statements->total();

        $similarity_results = null;
        if ($request->get('s')) {
            $similarity_results = $this->drive_in_service->getSimilarityWords($request->get('s'));
        }

        return view('statement.index', compact(
            'statements',
            'options',
            'total',
            'similarity_results'
        ));
    }

    /**
     * @param Request $request
     *
     * @return View|Factory|Application
     */
    public function search(Request $request): View|Factory|Application
    {
        $options = $this->prepareOptions();
        return view('statement.search', compact('options'));
    }

    /**
     * @param Request $request
     *
     * @return Factory|View|Application|RedirectResponse
     */
    public function create(Request $request): Factory|View|Application|RedirectResponse
    {
        // If you don't have a platform, we don't want you here.
        if(!$request->user()->platform) {
            return back()->withErrors('Your account is not associated with a platform.');
        }

        // If you are not allowed to create statements we also don't want you here.
        if(!$request->user()->can('create statements')) {
            return back()->withErrors('Your account is not able to create statements.');
        }

        $statement = new Statement();
        $statement->territorial_scope = [];

        $options = $this->prepareOptions();
        return view('statement.create', [
            'statement' => $statement,
            'options' => $options
        ]);
    }

    /**
     * @param Statement $statement
     *
     * @return Factory|View|Application
     */
    public function show(Statement $statement): Factory|View|Application
    {
        $statement_territorial_scope_country_names = $this->european_countries_service->getCountryNames($statement->territorial_scope);
        $statement_content_types = Statement::getEnumValues($statement->content_type);
        $statement_content_language = $this->european_languages_service->getName($statement->content_language ?? '');
        $statement_additional_categories = Statement::getEnumValues($statement->category_addition);
        sort($statement_territorial_scope_country_names);

        return view('statement.show', compact([
            'statement',
            'statement_territorial_scope_country_names',
            'statement_content_types',
            'statement_content_language',
            'statement_additional_categories'
        ]));

    }


    /**
     * @param StatementStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(StatementStoreRequest $request): RedirectResponse
    {


        $validated = $request->safe()->merge([
            'platform_id' => $request->user()->platform_id,
            'user_id' => $request->user()->id,
            'method' => Statement::METHOD_FORM
        ])->toArray();

        $validated['application_date'] = $this->sanitizeDate($validated['application_date'] ?? null);
        $validated['content_time'] = $this->sanitizeDate($validated['content_time'] ?? null);
        $validated['end_date'] = $this->sanitizeDate($validated['end_date'] ?? null);

        $validated['territorial_scope'] = $this->european_countries_service->filterSortEuropeanCountries($validated['territorial_scope'] ?? []);
        $validated['content_type'] = array_unique($validated['content_type']);
        sort($validated['content_type']);

        try {
            Statement::create($validated);
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), "statements_platform_id_puid_unique")
            ) {
                return back()->withInput()->withErrors([
                    'puid' => [
                        'The identifier given is not unique within this platform.'
                    ]
                ]);
            } else {
                Log::error('Statement Creation Query Exception Thrown: ' . $e->getMessage());
                back()->withInput()->withErrors(['exception' => 'An uncaught exception was thrown, support has been notified.']);
            }
        }

        return redirect()->route('statement.index')->with('success', 'The statement has been created.');
    }

    /**
     * @return array
     */
    private function prepareOptions(): array
    {
        // Prepare options for forms and selects and such.
        $countries = $this->mapForSelectWithKeys($this->european_countries_service->getOptionsArray());

        $languages = $this->mapForSelectWithKeys($this->european_languages_service->getAllLanguages(true));
        $languages_grouped = $this->mapForSelectWithKeys($this->european_languages_service->getAllLanguages(true, true));

        $eu_countries = EuropeanCountriesService::EUROPEAN_UNION_COUNTRY_CODES;
        $eea_countries = EuropeanCountriesService::EUROPEAN_ECONOMIC_AREA_COUNTRY_CODES;

        $automated_detections = $this->mapForSelectWithoutKeys(Statement::AUTOMATED_DETECTIONS);
        $automated_decisions = $this->mapForSelectWithoutKeys(Statement::AUTOMATED_DECISIONS);
        $incompatible_content_illegals = $this->mapForSelectWithoutKeys(Statement::INCOMPATIBLE_CONTENT_ILLEGALS);
        $content_types = $this->mapForSelectWithKeys(Statement::CONTENT_TYPES);
        $platforms = Platform::query()->orderBy('name', 'ASC')->get()->map(function($platform){
            return [
                'value' => $platform->id,
                'label' => $platform->name
            ];
        })->toArray();
        $decision_visibilities = $this->mapForSelectWithKeys(Statement::DECISION_VISIBILITIES);
        $decision_monetaries = $this->mapForSelectWithKeys(Statement::DECISION_MONETARIES);
        $decision_provisions = $this->mapForSelectWithKeys(Statement::DECISION_PROVISIONS);
        $decision_accounts = $this->mapForSelectWithKeys(Statement::DECISION_ACCOUNTS);
        $account_types = $this->mapForSelectWithKeys(Statement::ACCOUNT_TYPES);

        $decision_grounds = $this->mapForSelectWithKeys(Statement::DECISION_GROUNDS);
        $categories = $this->mapForSelectWithKeys(Statement::STATEMENT_CATEGORIES);
        $categories_addition = $this->mapForSelectWithKeys(Statement::STATEMENT_CATEGORIES);

        $illegal_content_fields = Statement::ILLEGAL_CONTENT_FIELDS;
        $incompatible_content_fields = Statement::INCOMPATIBLE_CONTENT_FIELDS;

        $source_types = $this->mapForSelectWithKeys(Statement::SOURCE_TYPES);

        return compact(
            'countries',
            'languages',
            'languages_grouped',
            'eea_countries',
            'eu_countries',
            'automated_detections',
            'automated_decisions',
            'incompatible_content_illegals',
            'decision_visibilities',
            'decision_monetaries',
            'decision_provisions',
            'decision_accounts',
            'account_types',
            'decision_grounds',
            'categories',
            'categories_addition',
            'illegal_content_fields',
            'incompatible_content_fields',
            'source_types',
            'content_types',
            'platforms',
        );
    }
}
