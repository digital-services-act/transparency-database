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
    public function territorial_scope_is_always_an_array(): void
    {
        $this->setUpFullySeededDatabase();
        $statement = Statement::all()->random()->first();
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


        // very bad json
        $statement->territorial_scope = 'hello mr. fox';
        $statement->save();
        $statement->refresh();

        $this->assertIsArray($statement->territorial_scope);
        $this->assertCount(0, $statement->territorial_scope);
    }

    /**
     * @return void
     * @test
     */
    public function territorial_scope_is_always_sorted(): void
    {
        $this->setUpFullySeededDatabase();
        $statement = Statement::all()->random()->first();

        // Store in non alpha order
        $statement->territorial_scope = ['SK', 'BE', 'AU'];
        $statement->save();
        $statement->refresh();

        // Get it back in alpha order
        $territorial_scope = $statement->territorial_scope;
        $this->assertEquals(["AU", "BE", "SK"], $territorial_scope);
    }
}
