<?php

namespace Database\Seeders;

use App\Models\Statement;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class StatementSeeder extends Seeder
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
        Statement::query()->delete();
        Statement::factory()->count(200)->create();
    }
}
