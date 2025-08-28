<?php

namespace Database\Seeders;

use App\Models\StatementAlpha;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class StatementAlphaSeeder extends Seeder
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
        self::resetStatements();
    }

    public static function resetStatements($statement_count = 0)
    {
        StatementAlpha::query()->delete();
        StatementAlpha::factory()->count($statement_count)->create();
    }
}
