<?php

namespace Database\Seeders;

use App\Models\Statement;
use Illuminate\Database\Seeder;

class StatementSeeder extends Seeder
{
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

        if ($statement_count <= 0) {
            return;
        }

        Statement::factory()->count($statement_count)->create();
    }
}
