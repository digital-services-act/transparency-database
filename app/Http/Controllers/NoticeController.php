<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoticeStoreRequest;
use App\Models\Notice;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class NoticeController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $notices = Notice::with('entities')->orderByDesc('id')->paginate(50);

        return view('notice.index', compact('notices'));
    }

    /**
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function create(Request $request)
    {
        // Dummy Notice to pre fill the form.
        $notice = new Notice();
        $notice->language = 'en';
        //$notice->date_sent = Carbon::now();
        $notice->countries_list = [];
        $notice->source = Notice::SOURCE_ARTICLE_16;
        $notice->payment_status = Notice::PAYMENT_STATUS_SUSPENSION;
        $notice->restriction_type = Notice::RESTRICTION_TYPE_REMOVED;
        $notice->automated_detection = Notice::AUTOMATED_DETECTIONS_YES;
        $notice->redress = Notice::REDRESS_INTERNAL_MECHANISM;

        $options = $this->prepareOptions();
        return view('notice.create', [
            'notice' => $notice,
            'options' => $options
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Notice $notice
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Notice $notice)
    {
        return view('notice.show', compact('notice'));
    }

    /**
     * @param NoticeStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(NoticeStoreRequest $request): RedirectResponse
    {

        $validated = $request->safe()->merge([
            'user_id' => auth()->user()->id,
            'method' => Notice::METHOD_FORM
        ])->toArray();

        $validated['date_sent'] = $this->sanitizeDate($validated['date_sent'] ?? null);
        $validated['date_enacted'] = $this->sanitizeDate($validated['date_enacted'] ?? null);
        $validated['date_abolished'] = $this->sanitizeDate($validated['date_abolished'] ?? null);

        $notice = Notice::create($validated);

        return redirect()->route('notice.index');
    }

    private function sanitizeDate($date)
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

        $sources = array_map(function($source) {return ['value' => $source, 'label' => $source];},Notice::SOURCES);
        $payment_statuses = array_map(function($payment_status) {return ['value' => $payment_status, 'label' => $payment_status];},Notice::PAYMENT_STATUES);
        $restriction_types = array_map(function($restriction_type) {return ['value' => $restriction_type, 'label' => $restriction_type];},Notice::RESTRICTION_TYPES);
        $automated_detections = array_map(function($automated_detection) {return ['value' => $automated_detection, 'label' => $automated_detection];},Notice::AUTOMATED_DETECTIONS);
        $redresses = array_map(function($redress) {return ['value' => $redress, 'label' => $redress];},Notice::REDRESSES);

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
