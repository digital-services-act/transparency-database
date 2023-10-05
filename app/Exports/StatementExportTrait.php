<?php

namespace App\Exports;
trait StatementExportTrait
{
    public function headings(): array
    {
        return [
            "uuid",

            "decision_visibility",
            "decision_visibility_other",
            "end_date_visibility_restriction",

            "decision_monetary",
            "decision_monetary_other",
            "end_date_monetary_restriction",

            "decision_provision",
            "end_date_service_restriction",

            "decision_account",
            "end_date_account_restriction",

            "account_type",

            "decision_ground",
            "decision_ground_reference_url",

            "illegal_content_legal_ground",
            "illegal_content_legal_ground",
            "incompatible_content_ground",
            "incompatible_content_explanation",
            "incompatible_content_illegal",

            "category",
            "category_addition",
            "category_specification",
            "category_specification_other",

            "content_type",
            "content_type_other",
            "content_language",
            "content_date",

            "territorial_scope",

            "application_date",

            "decision_facts",

            "source_type",
            "source_identity",

            "automated_detection",
            "automated_decision",

            "platform_name",
            "created_at"
        ];
    }

    public function map($statement): array
    {
        return [
            $statement->uuid,

            $statement->decision_visibility,
            $statement->decision_visibility_other,
            $statement->end_date_visibility_restriction,

            $statement->decision_monetary,
            $statement->decision_monetary_other,
            $statement->end_date_monetary_restriction,

            $statement->decision_provision,
            $statement->end_date_service_restriction,

            $statement->decision_account,
            $statement->end_date_account_restriction,

            $statement->account_type,

            $statement->decision_ground,
            $statement->decision_ground_reference_url,

            $statement->illegal_content_legal_ground,
            $statement->illegal_content_legal_ground,
            $statement->incompatible_content_ground,
            $statement->incompatible_content_explanation,
            $statement->incompatible_content_illegal,

            $statement->category,
            $statement->category_addition,
            $statement->category_specification,
            $statement->category_specification_other,

            $statement->content_type,
            $statement->content_type_other,
            $statement->content_language,
            $statement->content_date,

            $statement->territorial_scope,

            $statement->application_date,

            $statement->decision_facts,

            $statement->source_type,
            $statement->source_identity,

            $statement->automated_detection,
            $statement->automated_decision,

            $statement->platform_name,

            $statement->created_at,
        ];
    }
}