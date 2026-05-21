<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_an_error_on_first_or_create_with_no_email(): void
    {
        $this->expectException(\Exception::class);
        $result = User::firstOrCreateByAttributes([
            'name' => 'test',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_user_by_attributes(): void
    {
        $result = User::firstOrCreateByAttributes([
            'name' => 'test',
            'email' => 'test@test.com',
        ]);

        $this->assertInstanceOf(User::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_matches_users_by_email_case_insensitively_on_login(): void
    {
        $first = User::firstOrCreateByAttributes([
            'email' => 'John.Doe@ec.europa.eu',
        ]);

        $second = User::firstOrCreateByAttributes([
            'email' => 'john.doe@ec.europa.eu',
        ]);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('john.doe@ec.europa.eu', $first->fresh()->email);
        $this->assertSame(1, User::query()->where('email', 'john.doe@ec.europa.eu')->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_finds_a_pre_existing_mixed_case_user_on_login_without_creating_a_duplicate(): void
    {
        $existing = User::query()->newModelInstance();
        $existing->setRawAttributes(['email' => 'John.Doe@ec.europa.eu']);
        $existing->password = 'placeholder';
        $existing->save();

        $loggedIn = User::firstOrCreateByAttributes([
            'email' => 'john.doe@ec.europa.eu',
        ]);

        $this->assertSame($existing->id, $loggedIn->id);
        $this->assertSame(1, User::query()->whereRaw('lower(email) = ?', ['john.doe@ec.europa.eu'])->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_lowercases_email_when_assigned_directly(): void
    {
        $user = new User;
        $user->email = 'Mixed.Case@Example.COM';

        $this->assertSame('mixed.case@example.com', $user->email);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
