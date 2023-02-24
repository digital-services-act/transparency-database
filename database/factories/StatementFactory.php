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
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->text,
            'language' => $this->faker->randomElement(["en", "fr", "de", "es", "nl", "it"]),
            'date_sent' => Carbon::createMidnightDate($date_sent),
            'date_enacted' => Carbon::createMidnightDate($date_enacted),
            'date_abolished' => Carbon::createMidnightDate($date_abolished),
            'countries_list' => $this->faker->randomElements(["US","BE","GB","FR","DE","IT","NL","ES"], rand(1, 8)),
            'source' => $this->faker->randomElement(Statement::SOURCES),
//            'payment_status' => $this->faker->randomElement(Statement::PAYMENT_STATUES),
//            'restriction_type' => $this->faker->randomElement(Statement::RESTRICTION_TYPES),
            'restriction_type_other' => $this->faker->text,
            'automated_detection' => $this->faker->randomElement(Statement::AUTOMATED_DETECTIONS),
            'automated_detection_more' => $this->faker->text,
//            'illegal_content_legal_ground' => $this->faker->randomElement(Statement::IC_LEGAL_GROUNDS),
            'illegal_content_explanation' => $this->faker->text,
            'toc_contractual_ground' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'toc_explanation' => $this->faker->text,
            'user_id' => $user_id,
            'redress' => $this->faker->randomElement(Statement::REDRESSES),
            'redress_more' => $this->faker->text,
            'method' => $this->faker->randomElement([Statement::METHOD_API, Statement::METHOD_FORM]),
        ];
    }
}
