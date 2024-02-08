<?php

namespace App\Exports;

use App\Models\Statement;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StatementsDayExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;
    use StatementExportTrait;

    protected string $date;

    /**
     * @throws Exception
     */
    public function __construct(string $date)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);
        $today = Carbon::today();

        if ($date && $date < $today) {
            $this->date = $date->format('Y-m-d');
        } else {
            throw new Exception("When creating a day export you must supply a YYYY-MM-DD date and it needs to be in the past.");
        }

    }
    #[\Override]
    public function query(): \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        return Statement::query()->where('created_at', '>=', $this->date . ' 00:00:00')->where('created_at', '<=', $this->date . ' 23:59:59');
    }
}