<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Notice;

class NoticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->text,
            'language' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'date_sent' => Carbon::createMidnightDate($this->faker->date())->addHours($this->faker->numberBetween(3,23))->addMinutes($this->faker->numberBetween(0,59))->addSeconds($this->faker->numberBetween(0,59)),
            'date_enacted' => Carbon::createMidnightDate($this->faker->date())->addHours($this->faker->numberBetween(3,23))->addMinutes($this->faker->numberBetween(0,59))->addSeconds($this->faker->numberBetween(0,59)),
            'date_abolished' => Carbon::createMidnightDate($this->faker->date())->addHours($this->faker->numberBetween(3,23))->addMinutes($this->faker->numberBetween(0,59))->addSeconds($this->faker->numberBetween(0,59)),
            'countries_list' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'source' => $this->faker->randomElement(["Article 16","voluntary own-initiative investigation"]),
            'payment_status' => $this->faker->randomElement(["suspension","termination","other"]),
            'restriction_type' => $this->faker->randomElement(["removed","disabled","demoted","other"]),
            'restriction_type_other' => $this->faker->text,
            'automated_detection' => $this->faker->randomElement(["Yes","No","Partial"]),
            'automated_detection_more' => $this->faker->text,
            'illegal_content_legal_ground' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'illegal_content_explanation' => $this->faker->text,
            'toc_contractual_ground' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'toc_explanation' => $this->faker->text,
            'redress' => $this->faker->randomElement(["Internal Mechanism","Out Of Court Settlement","Other"]),
            'redress_more' => $this->faker->text,
        ];
    }
}
