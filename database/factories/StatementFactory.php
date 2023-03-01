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

        $date_sent = Carbon::createMidnightDate($this->faker->dateTimeBetween('-1 years'));
        $date_enacted = $date_sent->clone();
        $date_abolished = $date_sent->clone();

        $days_to_add = $this->faker->randomNumber(2);
        $days_to_add_add = $days_to_add + $this->faker->randomNumber(2);

        $date_enacted->addDays($days_to_add);
        $date_abolished->addDays($days_to_add_add);

        $user_id = User::all()->random()->id;

        return [


            'decision_taken' => $this->faker->randomElement(array_keys(Statement::DECISIONS)),
            'decision_ground' => $this->faker->randomElement(array_keys(Statement::DECISION_GROUNDS)),
            'illegal_content_legal_ground' => $this->faker->text(),
            'illegal_content_explanation' => $this->faker->text(),
            'incompatible_content_ground' => $this->faker->text(),
            'incompatible_content_explanation' => $this->faker->text(),

            'countries_list' => $this->faker->randomElements(Statement::EUROPEAN_COUNTRY_CODES, rand(1, 8)),

            'date_abolished' => Carbon::createMidnightDate($date_abolished),

            'source' => $this->faker->randomElement(array_keys(Statement::SOURCES)),
            'source_identity' => $this->faker->text(),
            'source_other' => $this->faker->text(),

            'automated_detection' => $this->faker->randomElement(Statement::AUTOMATED_DETECTIONS),

            'redress' => $this->faker->randomElement(array_keys(Statement::REDRESSES)),
            'redress_more' => $this->faker->text(),
            'user_id' => $user_id,
            'method' => $this->faker->randomElement([Statement::METHOD_API, Statement::METHOD_FORM]),
            'created_at' => $date_sent
        ];
    }
}
