<?php

namespace Database\Factories;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Statement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws \Exception
     */
    public function definition()
    {

        $create_date = Carbon::createMidnightDate($this->faker->dateTimeBetween('-2 years'));
        $content_date = $create_date->clone();
        $application_date = $create_date->clone();
        $end_date = $create_date->clone();
        $end_date_account_restriction = $create_date->clone();
        $end_date_monetary_restriction = $create_date->clone();
        $end_date_service_restriction = $create_date->clone();
        $end_date_visibility_restriction = $create_date->clone();


        $content_date->subDays(5);
        $application_date->subDays(4);
        $end_date->addDays(86);
        $end_date_account_restriction->addDays(33);
        $end_date_monetary_restriction->addDays(44);
        $end_date_service_restriction->addDays(55);
        $end_date_visibility_restriction->addDays(66);

        $create_date = $create_date->format('Y-n-j') . ' 00:00:00';
        $content_date = $content_date->format('Y-n-j') . ' 00:00:00';
        $application_date = $application_date->format('Y-n-j') . ' 00:00:00';
        $end_date = $end_date->format('Y-n-j') . ' 00:00:00';
        $end_date_account_restriction = $end_date_account_restriction->format('Y-n-j') . ' 00:00:00';
        $end_date_monetary_restriction = $end_date_monetary_restriction->format('Y-n-j') . ' 00:00:00';
        $end_date_service_restriction = $end_date_service_restriction->format('Y-n-j') . ' 00:00:00';
        $end_date_visibility_restriction = $end_date_visibility_restriction->format('Y-n-j') . ' 00:00:00';

        $dsa_platform = Platform::where('name', Platform::LABEL_DSA_TEAM)->first();
        $user = User::whereNot('platform_id', $dsa_platform->id)->get()->random();

        $decision_ground = $this->faker->randomElement(array_keys(Statement::DECISION_GROUNDS));

        return [

            'decision_visibility' => $this->faker->randomElements(array_keys(Statement::DECISION_VISIBILITIES)),
            'decision_visibility_other' => $this->faker->text(100),

            'decision_monetary' => $this->faker->randomElement(array_keys(Statement::DECISION_MONETARIES)),
            'decision_monetary_other' => $this->faker->text(100),

            'decision_provision' => $this->faker->randomElement(array_keys(Statement::DECISION_PROVISIONS)),
            'decision_account' => $this->faker->randomElement(array_keys(Statement::DECISION_ACCOUNTS)),
            'account_type' => $this->faker->randomElement(array_keys(Statement::ACCOUNT_TYPES)),

            'decision_ground' => $decision_ground,
            'decision_ground_reference_url' => $this->faker->url(),

            'content_type' => $this->faker->randomElements(array_keys(Statement::CONTENT_TYPES)),
            'content_type_other' => $this->faker->text(100),

            'category' => $this->faker->randomElement(array_keys(Statement::STATEMENT_CATEGORIES)),

            'illegal_content_legal_ground' => $decision_ground === 'DECISION_GROUND_ILLEGAL_CONTENT' ? $this->faker->realText(100) : null,
            'illegal_content_explanation' => $decision_ground === 'DECISION_GROUND_ILLEGAL_CONTENT' ? $this->faker->realText(500) : null,

            'incompatible_content_ground' => $decision_ground === 'DECISION_GROUND_INCOMPATIBLE_CONTENT' ? $this->faker->realText(100) : null,
            'incompatible_content_explanation' => $decision_ground === 'DECISION_GROUND_INCOMPATIBLE_CONTENT' ? $this->faker->realText(500) : null,

            'incompatible_content_illegal' => $this->faker->randomElement(Statement::INCOMPATIBLE_CONTENT_ILLEGALS),


            'puid' => $this->faker->uuid,

            'territorial_scope' => $this->faker->randomElements(EuropeanCountriesService::EUROPEAN_COUNTRY_CODES, rand(1, 30)),

            'content_language' => $this->faker->randomElement(EuropeanLanguagesService::EUROPEAN_LANGUAGE_CODES),

            'content_date' => $content_date,
            'application_date' => $application_date,
            'end_date' => $end_date,

            'end_date_account_restriction' => $end_date_account_restriction,
            'end_date_monetary_restriction' => $end_date_monetary_restriction,
            'end_date_service_restriction' => $end_date_service_restriction,
            'end_date_visibility_restriction' => $end_date_visibility_restriction,

            'source_type' => $this->faker->randomElement(array_keys(Statement::SOURCE_TYPES)),
            'source_identity' => $this->faker->text(100),

            'decision_facts' => $this->faker->realText(1000),

            'automated_detection' => $this->faker->randomElement(Statement::AUTOMATED_DETECTIONS),
            'automated_decision' => $this->faker->randomElement(array_keys(Statement::AUTOMATED_DECISIONS)),

            'platform_id' => $user->platform_id,
            'user_id' => $user->id,
            'method' => $this->faker->randomElement([Statement::METHOD_API, Statement::METHOD_FORM]),
            'created_at' => $create_date

        ];
    }


}
