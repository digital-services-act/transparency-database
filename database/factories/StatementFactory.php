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
     * @throws \Exception
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

        $full_statement = random_int(1, 10) > 9;

        $decision_ground = $this->faker->randomElement(array_keys(Statement::DECISION_GROUNDS));



        return [

            'decision_visibility' => $this->faker->randomElement(array_keys(Statement::DECISION_VISIBILITIES)),
            'decision_visibility_other' => $full_statement ? $this->faker->text(100): null,

            'decision_monetary' => $this->faker->randomElement(array_keys(Statement::DECISION_MONETARIES)),
            'decision_monetary_other' => $full_statement ? $this->faker->text(100): null,

            'decision_provision' => $this->faker->randomElement(array_keys(Statement::DECISION_PROVISIONS)),
            'decision_account' => $this->faker->randomElement(array_keys(Statement::DECISION_ACCOUNTS)),


            'decision_ground' => $decision_ground,

            'content_type' => $this->faker->randomElement(array_keys(Statement::CONTENT_TYPES)),
            'content_type_other' => $full_statement ? $this->faker->text(100): null,

            'category' => $this->faker->randomElement(array_keys(Statement::STATEMENT_CATEGORIES)),


            'illegal_content_legal_ground' => $decision_ground === 'DECISION_GROUND_ILLEGAL_CONTENT' ? $this->faker->realText(100) : null,
            'illegal_content_explanation' => $decision_ground === 'DECISION_GROUND_ILLEGAL_CONTENT' ? $this->faker->realText(500) : null,

            'incompatible_content_ground' => $decision_ground === 'DECISION_GROUND_INCOMPATIBLE_CONTENT' ? $this->faker->realText(100): null,
            'incompatible_content_explanation' => $decision_ground === 'DECISION_GROUND_INCOMPATIBLE_CONTENT' ? $this->faker->realText(500) : null,

            'incompatible_content_illegal' => $this->faker->randomElement(Statement::INCOMPATIBLE_CONTENT_ILLEGALS),

            'url' => $full_statement ? $this->faker->url : 'n/a',

            'countries_list' => $this->faker->randomElements(Statement::EUROPEAN_COUNTRY_CODES, rand(1, 8)),

            'start_date' => $start_date,
            'end_date' => $end_date,

            'source_type' => $this->faker->randomElement(array_keys(Statement::SOURCE_TYPES)),
            'source' => $full_statement ? $this->faker->text(100) : null,

            'decision_facts' => $full_statement ? $this->faker->realText(500) : 'n/a',

            'automated_detection' => $this->faker->randomElement(Statement::AUTOMATED_DETECTIONS),
            'automated_decision' => $this->faker->randomElement(Statement::AUTOMATED_DECISIONS),

            'platform_id' => $user->platform_id,
            'user_id' => $user->id,
            'method' => $this->faker->randomElement([Statement::METHOD_API, Statement::METHOD_FORM]),
            'created_at' => $create_date

        ];
    }

    public function spam()
    {
        return $this->state(function (array $attributes) {
            return [

                'decision_visibility' => 'DECISION_VISIBILITY_CONTENT_REMOVED',
                'decision_monetary' => null,
                'decision_ground' => 'DECISION_GROUND_INCOMPATIBLE_CONTENT',
                'content_type' => 'CONTENT_TYPE_TEXT',
                'category' => 'STATEMENT_CATEGORY_VIOLATION_TOS',

                'incompatible_content_ground' => 'spam',
                'incompatible_content_explanation' => 'spam',

                'incompatible_content_illegal' => $this->faker->randomElement(Statement::INCOMPATIBLE_CONTENT_ILLEGALS),

                'url' =>  'https//spam.com',

                'countries_list' => ["AT","BE","BG","HR","CY","CZ","DK","EE","FI","FR","DE","GR","HU","IE","IT","LV","LT","LU","MT","NL","PL","PT","RO","SK","SI","ES","SE"],

                'start_date' => Carbon::now()->format('Y-n-j') . ' 00:00:00',


                'source_type' => 'SOURCE_VOLUNTARY',

                'decision_facts' => 'spam',

                'automated_detection' => 'Yes',
                'automated_decision' => 'Yes',

                'platform_id' => 1,
                'user_id' => 1,
                'method' => 'API',


            ];
        });
    }


}
