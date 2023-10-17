<?php

namespace App\Services;

use App\Models\ContentDateAggregate;
use App\Models\Platform;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContentDateAggregateService
{

    /**
     * @throws Exception
     */
    public function compileDayTotals(Carbon $date): void
    {
        if ($date >= Carbon::today()) {
            throw new Exception('Date must be in the past when compiling application date aggregate day totals');
        }

        $start = $date->format('Y-m-d 00:00:00');
        $end = $date->format('Y-m-d 23:59:59');
        $date = $date->format('Y-m-d');

        // Delete any existing
        ContentDateAggregate::query()->where('date', $date)->delete();

        $results = DB::table('statements')
                     ->selectRaw('count(id) as total, platform_id, decision_visibility, decision_monetary, decision_provision, decision_account, category, decision_ground, automated_detection, automated_decision, content_type, source_type')
                     ->groupByRaw('platform_id, decision_visibility, decision_monetary, decision_provision, decision_account, category, decision_ground, automated_detection, automated_decision, content_type, source_type')
                     ->where('content_date', '>=', $start)
                     ->where('content_date', '<=', $end)->get();

        $platforms = Platform::all()->pluck('name', 'id')->toArray();

        foreach ($results as $result)
        {
            ContentDateAggregate::create([
                'platform_id' => $result->platform_id,
                'platform_name' => $platforms[$result->platform_id],
                'date' => $date,
                'decision_visibility' => $result->decision_visibility,
                'decision_monetary' => $result->decision_monetary,
                'decision_provision' => $result->decision_provision,
                'decision_account' => $result->decision_account,
                'category' => $result->category,
                'decision_ground' => $result->decision_ground,
                'automated_detection' => $result->automated_detection,
                'automated_decision' => $result->automated_decision,
                'content_type' => $result->content_type,
                'source_type' => $result->source_type,
                'total' => $result->total
            ]);
        }
    }
}