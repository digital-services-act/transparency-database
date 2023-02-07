<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class StatementController extends Controller
{
    /**
     * @return View|Factory|Application
     */
    public function index(): View|Factory|Application
    {
        $statements = Statement::with('entities')->orderBy('id', 'desc')->paginate(50);
        return view('statement.index', compact('statements'));
    }

    /**
     * @return Factory|View|Application
     */
    public function create(): Factory|View|Application
    {
        // Dummy Statement to pre fill the form.
        $statement = new Statement();
        $statement->language = 'en';
        //$statement->date_sent = Carbon::now();
        $statement->countries_list = [];
        $statement->source = Statement::SOURCE_ARTICLE_16;
        $statement->payment_status = Statement::PAYMENT_STATUS_SUSPENSION;
        $statement->restriction_type = Statement::RESTRICTION_TYPE_REMOVED;
        $statement->automated_detection = Statement::AUTOMATED_DETECTIONS_YES;
        $statement->redress = Statement::REDRESS_INTERNAL_MECHANISM;

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
        return view('statement.show', compact('statement'));
    }

    /**
     * @param StatementStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(StatementStoreRequest $request): RedirectResponse
    {
        $validated = $request->safe()->merge([
            'user_id' => auth()->user()->id,
            'method' => Statement::METHOD_FORM
        ])->toArray();

        $validated['date_sent'] = $this->sanitizeDate($validated['date_sent'] ?? null);
        $validated['date_enacted'] = $this->sanitizeDate($validated['date_enacted'] ?? null);
        $validated['date_abolished'] = $this->sanitizeDate($validated['date_abolished'] ?? null);

        $statement = Statement::create($validated);

        $url = route('statement.show', [$statement]);
        return redirect()->route('statement.index')->with('success', 'The statement has been created: <a href="' . $url . '">' . $statement->title . '</a>');
    }

    private function sanitizeDate($date): ?string
    {
        return $date ? Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d 00:00:00') : null;
    }

    /**
     * @return array
     */
    private function prepareOptions(): array
    {
        // Prepare Options
        $languages = Languages::getNames();
        $languages = array_map(function($key, $value){
            return ['value' => $key, 'label' => $value];
        }, array_keys($languages), array_values($languages));

        $countries = Countries::getNames();
        $countries = array_map(function($key, $value){
            return ['value' => $key, 'label' => $value];
        }, array_keys($countries), array_values($countries));

        $sources = array_map(function($source) {return ['value' => $source, 'label' => $source];},Statement::SOURCES);
        $payment_statuses = array_map(function($payment_status) {return ['value' => $payment_status, 'label' => $payment_status];},Statement::PAYMENT_STATUES);
        $restriction_types = array_map(function($restriction_type) {return ['value' => $restriction_type, 'label' => $restriction_type];},Statement::RESTRICTION_TYPES);
        $automated_detections = array_map(function($automated_detection) {return ['value' => $automated_detection, 'label' => $automated_detection];},Statement::AUTOMATED_DETECTIONS);
        $redresses = array_map(function($redress) {return ['value' => $redress, 'label' => $redress];},Statement::REDRESSES);

        return compact(
            'countries',
            'languages',
            'automated_detections',
            'payment_statuses',
            'redresses',
            'restriction_types',
            'sources'
        );
    }
}
