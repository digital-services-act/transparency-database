<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_throws_an_error_on_first_or_create_with_no_email(): void
    {
        $this->expectException(\Exception::class);
        $result = User::firstOrCreateByAttributes([
            'name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function it_creates_a_user_by_attributes(): void
    {
        $result = User::firstOrCreateByAttributes([
            'name' => 'test',
            'email' => 'test@test.com',
        ]);

        $this->assertInstanceOf(User::class, $result);
    }

    /**
     * @test
     */
    public function it_correctly_sees_valid_token_or_not(): void
    {
        $user = User::firstOrCreateByAttributes([
            'name' => 'test',
            'email' => 'test@test.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertFalse($user->hasValidApiToken());
        $user->createToken(User::API_TOKEN_KEY);
        $this->assertTrue($user->hasValidApiToken());
    }
}
