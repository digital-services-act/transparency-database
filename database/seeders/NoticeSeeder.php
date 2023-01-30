<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\Notice;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{


    protected $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Notice::factory()->count(10)
//            ->hasAttached(
//                Entity::where('id',rand(1,20))->get(),
//                ['role' => $this->faker->randomElement(["principal", "agent", "recipient", "sender", "target", "issuing_court", "plaintiff", "defendant","submitter"])]
//            )
            ->create();
    }
}
