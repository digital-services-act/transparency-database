<?php

namespace Database\Factories;

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

        $base_date = Carbon::createMidnightDate($this->faker->dateTimeBetween('-1 years'));

        $start_date = $end_date = $base_date->format('Y-m-d G:i:s');

        $user_id = User::all()->random()->id;

        return [

            'decision_visibility' => $this->faker->randomElement(array_keys(Statement::DECISIONS_VISIBILITY)),
            'decision_monetary' => $this->faker->randomElement(array_keys(Statement::DECISIONS_MONETARY)),
            'decision_provision' => $this->faker->randomElement(array_keys(Statement::DECISIONS_PROVISION)),
            'decision_account' => $this->faker->randomElement(array_keys(Statement::DECISIONS_ACCOUNT)),


            'decision_ground' => $this->faker->randomElement(array_keys(Statement::DECISION_GROUNDS)),
            'content_type' => $this->faker->randomElement(array_keys(Statement::CONTENT_TYPES)),
            'category' => $this->faker->randomElement(array_keys(Statement::SOR_CATEGORIES)),

            'illegal_content_legal_ground' => $this->faker->text(100),
            'illegal_content_explanation' => $this->faker->realText(500),
            'incompatible_content_ground' => $this->faker->text(100),
            'incompatible_content_explanation' => $this->faker->realText(500),
            'incompatible_content_illegal' => $this->faker->boolean,

            'url' => $this->faker->url,

            'countries_list' => $this->faker->randomElements(Statement::EUROPEAN_COUNTRY_CODES, rand(1, 8)),

            'start_date' => $start_date,
            'end_date' => $end_date,

            'source' => $this->faker->randomElement(array_keys(Statement::SOURCES)),
            'decision_facts' => $this->faker->realText(500),

            'automated_detection' => $this->faker->randomElement(Statement::AUTOMATED_DETECTIONS),
            'automated_decision' => $this->faker->randomElement(Statement::AUTOMATED_DECISIONS),
            'automated_takedown' => $this->faker->randomElement(Statement::AUTOMATED_TAKEDOWNS),


            'user_id' => $user_id,
            'method' => $this->faker->randomElement([Statement::METHOD_API, Statement::METHOD_FORM]),
            'created_at' => $base_date
        ];
    }
}
