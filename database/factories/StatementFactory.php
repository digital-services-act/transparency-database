<?php

namespace Database\Factories;

use App\Models\Platform;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Statement;

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
     */
    public function definition()
    {

        $create_date = Carbon::createMidnightDate($this->faker->dateTimeBetween('-2 years'));
        $start_date = $create_date->clone();
        $end_date = $start_date->addDays(90);

        $create_date = $create_date->format('Y-n-j') . ' 00:00:00';
        $start_date = $start_date->format('Y-n-j') . ' 00:00:00';
        $end_date = $end_date->format('Y-n-j') . ' 00:00:00';


        $dsa_platform = Platform::where('name', Platform::LABEL_DSA_TEAM)->first();

        $user = User::whereNot('platform_id', $dsa_platform->id)->get()->random();

        return [

            'decision_visibility' => $this->faker->randomElement(array_keys(Statement::DECISION_VISIBILITIES)),
            'decision_visibility_other' => $this->faker->text(100),

            'decision_monetary' => $this->faker->randomElement(array_keys(Statement::DECISION_MONETARIES)),
            'decision_monetary_other' => $this->faker->text(100),

            'decision_provision' => $this->faker->randomElement(array_keys(Statement::DECISION_PROVISIONS)),
            'decision_account' => $this->faker->randomElement(array_keys(Statement::DECISION_ACCOUNTS)),


            'decision_ground' => $this->faker->randomElement(array_keys(Statement::DECISION_GROUNDS)),

            'content_type' => $this->faker->randomElement(array_keys(Statement::CONTENT_TYPES)),
            'content_type_other' => $this->faker->text(100),

            'category' => $this->faker->randomElement(array_keys(Statement::STATEMENT_CATEGORIES)),

            'illegal_content_legal_ground' => $this->faker->text(100),
            'illegal_content_explanation' => $this->faker->realText(500),

            'incompatible_content_ground' => $this->faker->text(100),
            'incompatible_content_explanation' => $this->faker->realText(500),

            'incompatible_content_illegal' => $this->faker->randomElement(Statement::INCOMPATIBLE_CONTENT_ILLEGALS),

            'url' => $this->faker->url,

            'countries_list' => $this->faker->randomElements(Statement::EUROPEAN_COUNTRY_CODES, rand(1, 8)),

            'start_date' => $start_date,
            'end_date' => $end_date,

            'source_type' => $this->faker->randomElement(array_keys(Statement::SOURCE_TYPES)),
            'source' => $this->faker->text(100),

            'decision_facts' => $this->faker->realText(500),

            'automated_detection' => $this->faker->randomElement(Statement::AUTOMATED_DETECTIONS),
            'automated_decision' => $this->faker->randomElement(Statement::AUTOMATED_DECISIONS),

            'platform_id' => $user->platform_id,
            'user_id' => $user->id,
            'method' => $this->faker->randomElement([Statement::METHOD_API, Statement::METHOD_FORM]),
            'created_at' => $create_date

        ];
    }
}
