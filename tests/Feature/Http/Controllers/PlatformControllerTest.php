<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

class PlatformControllerTest extends TestCase
{
    use AdditionalAssertions;
    use RefreshDatabase;
    /**
     * @return void
     * @test
     */
    public function deleting_platform_deletes_the_rest(): void
    {

        $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $total_users_start = User::count();

        $statement = Statement::all()->random(); // Grab one
        $user = $statement->user;
        $platform = $user->platform;

        $platform_count = Platform::all()->count();
        $statement_count = $platform->statements()->get()->count(); // at least 1
        $user_count = $platform->users()->get()->count(); // at least 1

        $dsa_platform = Platform::getDsaPlatform();
        $dsa_platform_statement_count = $dsa_platform->statements()->count();
        $this->assertEquals(0, $dsa_platform_statement_count); // DSA should have no statements

        // delete the platform and assert we deleted
        $this->delete(route('platform.destroy', [$platform]))->assertRedirect(route('platform.index'));

        // Statements should have moved to DSA
        $dsa_platform_statement_count = $dsa_platform->statements()->count(); // DSA should have statements
        $this->assertEquals($statement_count, $dsa_platform_statement_count);

        $this->assertCount(10, Statement::all());
        $this->assertCount($total_users_start - $user_count, User::all());
        $this->assertCount($platform_count - 1, Platform::all());
    }

    /**
     * @test
     */
    public function register_store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PlatformController::class,
            'platformRegisterStore',
            \App\Http\Requests\PlatformRegisterStoreRequest::class
        );
    }



}
