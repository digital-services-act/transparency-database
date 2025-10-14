<?php

namespace App\Exports;

trait StatementExportTrait
{
    /**
     * headings for the full csv export and the mini csv export
     * @return array
     */
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
            "illegal_content_explanation",
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
            "content_id_ean",

            "territorial_scope",

            "application_date",

            "decision_facts",

            "source_type",
            "source_identity",

            "automated_detection",
            "automated_decision",

            "platform_name",
            "platform_uid",
            "created_at",
        ];
    }

    /**
     * headings for the mini csv export
     */
    public function headingsLight(): array
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
            //"illegal_content_explanation",
            "incompatible_content_ground",
            //"incompatible_content_explanation",
            "incompatible_content_illegal",

            "category",
            "category_addition",
            "category_specification",
            "category_specification_other",

            "content_type",
            "content_type_other",
            "content_language",
            "content_date",
            "content_id_ean",

            //"territorial_scope",

            "application_date",

            //"decision_facts",

            "source_type",
            "source_identity",

            "automated_detection",
            "automated_decision",

            "platform_name",
            "platform_uid",
            "created_at",
        ];
    }

    /**
     * This function maps a eloquent model to an array for the mini CSV export.
     * @param mixed $statement
     * @return array
     */
    public function map($statement): array
    {
        $out = [
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
            $statement->getRawOriginal('illegal_content_explanation'),
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
            $statement->getRawOriginal('content_id_ean'),

            $statement->getRawOriginal('territorial_scope'),

            $statement->getRawOriginal('application_date'),

            $statement->getRawOriginal('decision_facts'),

            $statement->getRawOriginal('source_type'),
            $statement->getRawOriginal('source_identity'),

            $statement->getRawOriginal('automated_detection'),
            $statement->getRawOriginal('automated_decision'),

            $statement->platform ? $statement->platform->getRawOriginal('name') : '',
            $statement->getRawOriginal('puid'),

            $statement->getRawOriginal('created_at'),
        ];

        foreach ($out as $key => $value) {
            $value = trim($value);
            $value = preg_replace('~[\r\n]+~', ' ', $value);
            $value = str_replace("\\\"", '"', $value);
            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * This function maps a raw sql query statement for full csv, not an eloquent model.
     * @param mixed $statement
     * @param mixed $platforms
     * @return array
     */
    public function mapRaw($statement, $platforms, $table = 'statements_beta'): array
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
            $statement->illegal_content_explanation,
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
            // $statement->content_id_ean,
            ...($table === 'statements_beta' ? [$statement->content_id_ean] : []),

            $statement->territorial_scope,

            $statement->application_date,

            $statement->decision_facts,

            $statement->source_type,
            $statement->source_identity,

            $statement->automated_detection,
            $statement->automated_decision,

            $platforms[$statement->platform_id],
            $statement->puid,

            $statement->created_at,
        ];
    }

    /**
     * This function maps a raw sql query statement for light csv export, not an eloquent model.
     * @param mixed $statement
     * @param mixed $platforms
     * @return array
     */
    public function mapRawLight($statement, $platforms, $table = 'statements_beta'): array
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
            //$statement->illegal_content_explanation,
            $statement->incompatible_content_ground,
            //$statement->incompatible_content_explanation,
            $statement->incompatible_content_illegal,

            $statement->category,
            $statement->category_addition,
            $statement->category_specification,
            $statement->category_specification_other,

            $statement->content_type,
            $statement->content_type_other,
            $statement->content_language,
            $statement->content_date,
            // $statement->content_id_ean,
            ...($table === 'statements_beta' ? [$statement->content_id_ean] : []),

            //$statement->territorial_scope,

            $statement->application_date,

            //$statement->decision_facts,

            $statement->source_type,
            $statement->source_identity,

            $statement->automated_detection,
            $statement->automated_decision,

            $platforms[$statement->platform_id],
            $statement->puid,

            $statement->created_at,
        ];
    }
}
