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
        self::resetStatements();
    }

    public static function resetStatements($statement_count = 0)
    {
        Statement::query()->delete();
        Statement::factory()->count($statement_count)->create();
    }
}
