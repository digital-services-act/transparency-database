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
            $statement->getRawOriginal('uuid'),

            $statement->getRawOriginal('decision_visibility'),
            $statement->getRawOriginal('decision_visibility_other'),
            $statement->getRawOriginal('end_date_visibility_restriction'),

            $statement->getRawOriginal('decision_monetary'),
            $statement->getRawOriginal('decision_monetary_other'),
            $statement->getRawOriginal('end_date_monetary_restriction'),

            $statement->getRawOriginal('decision_provision'),
            $statement->getRawOriginal('end_date_service_restriction'),

            $statement->getRawOriginal('decision_account'),
            $statement->getRawOriginal('end_date_account_restriction'),

            $statement->getRawOriginal('account_type'),

            $statement->getRawOriginal('decision_ground'),
            $statement->getRawOriginal('decision_ground_reference_url'),

            $statement->getRawOriginal('illegal_content_legal_ground'),
            $statement->getRawOriginal('illegal_content_legal_ground'),
            $statement->getRawOriginal('incompatible_content_ground'),
            $statement->getRawOriginal('incompatible_content_explanation'),
            $statement->getRawOriginal('incompatible_content_illegal'),

            $statement->getRawOriginal('category'),
            $statement->getRawOriginal('category_addition'),
            $statement->getRawOriginal('category_specification'),
            $statement->getRawOriginal('category_specification_other'),

            $statement->getRawOriginal('content_type'),
            $statement->getRawOriginal('content_type_other'),
            $statement->getRawOriginal('content_language'),
            $statement->getRawOriginal('content_date'),

            $statement->getRawOriginal('territorial_scope'),

            $statement->getRawOriginal('application_date'),

            $statement->getRawOriginal('decision_facts'),

            $statement->getRawOriginal('source_type'),
            $statement->getRawOriginal('source_identity'),

            $statement->getRawOriginal('automated_detection'),
            $statement->getRawOriginal('automated_decision'),

            $statement->getRawOriginal('platform_name'),

            $statement->getRawOriginal('created_at'),
        ];
    }
}