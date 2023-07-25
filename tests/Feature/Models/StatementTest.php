<?php

namespace Tests\Feature\Models;


use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     * @test
     */
    public function territorial_scope_is_always_an_array()
    {
        $this->setUpFullySeededDatabase();
        $statement = Statement::all()->random()->first();
        $a = $statement->territorial_scope;
        $this->assertIsArray($statement->territorial_scope);
        $this->assertNotCount(0, $statement->territorial_scope);

        $statement->territorial_scope = null;
        $statement->save();
        $statement->refresh();

        $this->assertIsArray($statement->territorial_scope);
        $this->assertCount(0, $statement->territorial_scope);

        // empty array
        $statement->territorial_scope = [];
        $statement->save();
        $statement->refresh();

        $this->assertIsArray($statement->territorial_scope);
        $this->assertCount(0, $statement->territorial_scope);
    }
}
