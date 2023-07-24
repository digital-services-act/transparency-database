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
    public function countries_list_does_not_break()
    {
        $this->setUpFullySeededDatabase();
        /** @var Statement $statement */
        $statement = Statement::all()->random()->first();
        $this->assertNotNull($statement);

        // The factory is always putting some countries here.
        $countries_list = $statement->countries_list;
        $this->assertIsArray($countries_list);

        $count = count($countries_list);

        $this->assertNotCount(0, $countries_list);

        $country_names_list = $statement->getCountriesListNames();

        $this->assertIsArray($country_names_list);
        $this->assertCount($count, $country_names_list);

        // Now null this out and make sure that we don't blow up.
        $statement->countries_list = null;
        $statement->save();
        $country_names_list = $statement->getCountriesListNames();
        $this->assertCount(0, $country_names_list);


        // Now try with bad iso codes
        $statement->countries_list = ["XX", "ZZ", "II"];
        $statement->save();
        $country_names_list = $statement->getCountriesListNames();
        $this->assertCount(3, $country_names_list);
        $this->assertEquals($country_names_list, ["Unknown", "Unknown", "Unknown"]);


        // Now test with all countries
        $statement->countries_list = Statement::EUROPEAN_COUNTRY_CODES;
        $statement->save();
        $country_names_list = $statement->getCountriesListNames();
        $this->assertCount(1, $country_names_list);
        $this->assertEquals($country_names_list, ["European Union"]);

    }
}
