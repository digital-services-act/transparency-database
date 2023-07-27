<?php

namespace App\Exports;

use App\Models\Statement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StatementsExport implements FromCollection, WithHeadings, WithMapping
{

    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Statement::all();
    }

    public function headings(): array
    {
        return [
            'uuid',
            'url',
            'decision_visibility',
            'decision_visibility_other',
            'decision_monetary',
            'decision_monetary_other',
            'decision_provision',
            'decision_account',
            'decision_ground',
            'category',
            'content_type',
            'content_type_other',
            'illegal_content_legal_ground',
            'illegal_content_explanation',
            'incompatible_content_ground',
            'incompatible_content_explanation',
            'incompatible_content_illegal',
            'territorial_scope',
            'start_date',
            'end_date',
            'decision_facts',
            'source_type',
            'source',
            'automated_detection',
            'automated_decision',
            'user_id',
            'platform_id',
            'method',
            'created_at',
            'updated_at',
            'deleted_at'
        ];
    }

    public function map($statement): array
    {
        return [
            $statement->uuid,
            $statement->url,
            $statement->decision_visibility,
            $statement->decision_visibility_other,
            $statement->decision_monetary,
            $statement->decision_monetary_other,
            $statement->decision_provision,
            $statement->decision_account,
            $statement->decision_ground,
            $statement->category,
            $statement->content_type,
            $statement->content_type_other,
            $statement->illegal_content_legal_ground,
            $statement->illegal_content_explanation,
            $statement->incompatible_content_ground,
            $statement->incompatible_content_explanation,
            $statement->incompatible_content_illegal,
            $statement->territorial_scope,
            $statement->start_date,
            $statement->end_date,
            $statement->decision_facts,
            $statement->source_type,
            $statement->source,
            $statement->automated_detection,
            $statement->automated_decision,
            $statement->user_id,
            $statement->platform_id,
            $statement->method,
            $statement->created_at,
            $statement->updated_at,
            $statement->deleted_at
        ];
    }
}
