<?php

namespace App\Http\Controllers;

use App\Exceptions\PuidNotUniqueSingleException;
use App\Exports\StatementsExport;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\DriveInService;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use App\Services\PlatformQueryService;
use App\Services\PlatformUniqueIdService;
use App\Services\StatementElasticConnectionService;
use App\Services\StatementElasticIndexerService;
use App\Services\StatementElasticSearchService;
use App\Services\StatementQueryService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Excel;

class StatementController extends Controller
{
    use Sanitizer;

    public function __construct(
        protected StatementQueryService $statement_query_service,
        protected StatementElasticConnectionService $statement_elastic_connection_service,
        protected StatementElasticSearchService $statement_elastic_search_service,
        protected EuropeanCountriesService $european_countries_service,
        protected EuropeanLanguagesService $european_languages_service,
        protected DriveInService $drive_in_service,
        protected PlatformUniqueIdService $puidService,
        protected PlatformQueryService $platform_query_service,
    ) {}

    public function index(Request $request): View|Factory|Application
    {
        // Limit the page query var to 200, other wise opensearch can error out on max result window.

        $pagination_per_page = 50;
        $page = min(max((int) $request->get('page', 1), 1), 200);

        $options = $this->prepareOptions(true);

        $setup = $this->paginatedStatements($request, $page, $pagination_per_page);
        $statements = $setup['statements'];
        $total = $setup['total'];

        $similarity_results = null;
        $parameters = $request->query();
        unset($parameters['page']);
        $statements->setPath(route('statement.index'));
        $statements->appends($parameters);

        return view('statement.index', [
            'statements' => $statements,
            'options' => $options,
            'total' => $total,
            'similarity_results' => $similarity_results,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $setup = $this->setupQuery($request, 0, 1000);

        $statements = $setup['statements'];

        $export = new StatementsExport;
        $export->setCollection($statements->orderBy('created_at', 'DESC')->get());

        return $export->download('statements-of-reason.csv', Excel::CSV);
    }

    private function paginatedStatements(Request $request, int $page, int $perPage): array
    {
        if ($this->statement_elastic_connection_service->isConfigured()) {
            $elastic_results = $this->statement_elastic_search_service->query($request->query(), [], $page - 1, $perPage);
            $total = $elastic_results['total'];
            $statements = $elastic_results['statements']->orderBy('created_at', 'DESC')->get();
            $pagination_max = min(10000, $total);

            return [
                'statements' => new LengthAwarePaginator($statements, $pagination_max, $perPage, $page),
                'total' => $total,
            ];
        }

        $statements = $this->statement_query_service
            ->query($request->query())
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'statements' => $statements,
            'total' => $statements->total(),
        ];
    }

    private function setupQuery(Request $request, int $page, int $perPage): array
    {
        // We have to ignore this in code coverage because the elastic is not available in the unit tests
        if ($this->statement_elastic_connection_service->isConfigured()) {
            $filters = $request->query();

            $elastic_results = $this->statement_elastic_search_service->query($filters, [], $page, $perPage);
            $statements = $elastic_results['statements'];
            $total = $elastic_results['total'];
        } else {
            // This should never happen,
            // raw queries on the statement table is very bad
            // maybe ok for a local dev sure
            $statements = $this->statement_query_service->query($request->query());
            $total = (clone $statements)->count();
            $statements->offset($page * $perPage)->limit($perPage);
        }

        return [
            'statements' => $statements,
            'total' => $total,
        ];
    }

    public function search(Request $request): View|Factory|Application
    {
        $options = $this->prepareOptions(true);

        return view('statement.search', ['options' => $options]);
    }

    public function create(Request $request): Factory|View|Application|RedirectResponse
    {
        // If you don't have a platform, we don't want you here.
        if (! $request->user()->platform) {
            return back()->withErrors('Your account is not associated with a platform.');
        }
        $statement = new Statement;
        $statement->territorial_scope = [];

        $options = $this->prepareOptions();

        return view('statement.create', [
            'statement' => $statement,
            'options' => $options,
        ]);
    }

    public function show(Statement $statement): Factory|View|Application
    {

        $view = 'statement.show';

        $statement_content_types = Statement::getEnumValues($statement->content_type);
        $statement_additional_categories = Statement::getEnumValues($statement->category_addition);
        $statement_visibility_decisions = Statement::getEnumValues($statement->decision_visibility);
        $category_specifications = Statement::getEnumValues($statement->category_specification);

        $statement_territorial_scope_country_names = $this->european_countries_service->getCountryNames($statement->territorial_scope);
        sort($statement_territorial_scope_country_names);
        $statement_content_language = $this->european_languages_service->getName($statement->content_language ?? '');

        return view($view, [
            'statement' => $statement,
            'statement_territorial_scope_country_names' => $statement_territorial_scope_country_names,
            'statement_content_types' => $statement_content_types,
            'statement_content_language' => $statement_content_language,
            'statement_additional_categories' => $statement_additional_categories,
            'statement_visibility_decisions' => $statement_visibility_decisions,
            'category_specifications' => $category_specifications,
        ]);
    }

    public function store(StatementStoreRequest $request, StatementElasticIndexerService $statement_elastic_indexer_service): RedirectResponse
    {

        $validated = $request->safe()->merge([
            'platform_id' => $request->user()->platform_id,
            'user_id' => $request->user()->id,
            'method' => Statement::METHOD_FORM,
        ])->toArray();

        $validated = $this->sanitizeData($validated);

        try {
            $statement = $this->puidService->runWithReservedPuid(
                $validated['platform_id'],
                $validated['puid'],
                static fn () => Statement::create($validated)
            );
        } catch (PuidNotUniqueSingleException $e) {
            return redirect()->route('statement.index')->with('error', 'The PUID is not unique in the database');
        }

        $env = config('app.env');
        if ($env !== 'production' && $this->statement_elastic_connection_service->isConfigured()) {
            // If we are not production and
            // If we have elasticsearch configured, we want to index the new statement
            // right away so it appears in search results immediately.
            $statement_elastic_indexer_service->indexStatement($statement);
        }

        return redirect()->route('statement.show', [$statement])->with('success', 'The statement has been created. <a href="/statement/'.$statement->uuid.'">Click here to view it.</a>');
    }

    private function prepareOptions($noval_on_select = false): array
    {
        // Prepare options for forms and selects and such.
        $countries = $this->mapForSelectWithKeys($this->european_countries_service->getOptionsArray());
        // dd($countries);

        $languages = $this->mapForSelectWithKeys($this->european_languages_service->getAllLanguages(true), $noval_on_select);
        $languages_grouped = $this->mapForSelectWithKeys($this->european_languages_service->getAllLanguages(true, true));

        $eu_countries = EuropeanCountriesService::EUROPEAN_UNION_COUNTRY_CODES;
        $eea_countries = EuropeanCountriesService::EUROPEAN_ECONOMIC_AREA_COUNTRY_CODES;

        $automated_detections = $this->mapForSelectWithoutKeys(Statement::AUTOMATED_DETECTIONS);
        $automated_decisions = $this->mapForSelectWithKeys(Statement::AUTOMATED_DECISIONS);
        $incompatible_content_illegals = $this->mapForSelectWithoutKeys(Statement::INCOMPATIBLE_CONTENT_ILLEGALS, $noval_on_select);
        $content_types = $this->mapForSelectWithKeys(Statement::CONTENT_TYPES);
        $platforms = $this->platform_query_service->getPlatformDropDownOptions();
        $decision_visibilities = $this->mapForSelectWithKeys(Statement::DECISION_VISIBILITIES, $noval_on_select);
        $decision_monetaries = $this->mapForSelectWithKeys(Statement::DECISION_MONETARIES, $noval_on_select);
        $decision_provisions = $this->mapForSelectWithKeys(Statement::DECISION_PROVISIONS, $noval_on_select);
        $decision_accounts = $this->mapForSelectWithKeys(Statement::DECISION_ACCOUNTS, $noval_on_select);
        $account_types = $this->mapForSelectWithKeys(Statement::ACCOUNT_TYPES, $noval_on_select);
        $category_specifications = $this->mapForSelectWithKeys(Statement::KEYWORDS, $noval_on_select);

        $decision_grounds = $this->mapForSelectWithKeys(Statement::DECISION_GROUNDS);
        $categories = $this->mapForSelectWithKeys(Statement::STATEMENT_CATEGORIES);
        $categories_addition = $this->mapForSelectWithKeys(Statement::STATEMENT_CATEGORIES, $noval_on_select);

        $illegal_content_fields = Statement::ILLEGAL_CONTENT_FIELDS;
        $incompatible_content_fields = Statement::INCOMPATIBLE_CONTENT_FIELDS;

        $source_types = $this->mapForSelectWithKeys(Statement::SOURCE_TYPES);

        return ['countries' => $countries, 'languages' => $languages, 'languages_grouped' => $languages_grouped, 'eea_countries' => $eea_countries, 'eu_countries' => $eu_countries, 'automated_detections' => $automated_detections, 'automated_decisions' => $automated_decisions, 'incompatible_content_illegals' => $incompatible_content_illegals, 'decision_visibilities' => $decision_visibilities, 'decision_monetaries' => $decision_monetaries, 'decision_provisions' => $decision_provisions, 'decision_accounts' => $decision_accounts, 'account_types' => $account_types, 'decision_grounds' => $decision_grounds, 'categories' => $categories, 'categories_addition' => $categories_addition, 'illegal_content_fields' => $illegal_content_fields, 'incompatible_content_fields' => $incompatible_content_fields, 'source_types' => $source_types, 'content_types' => $content_types, 'platforms' => $platforms, 'category_specifications' => $category_specifications];
    }
}
