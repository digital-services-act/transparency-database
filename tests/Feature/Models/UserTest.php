<?php

namespace Tests\Feature\Models;

use App\Models\Invitation;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     * @test
     */
    public function user_should_have_pending_invitation(): void
    {
        $this->setUpFullySeededDatabase();

        $user = User::factory()->create([
            'email' => "invited@testing.org",
        ]);

        $this->assertNull($user->invitation);

        Invitation::factory()->create(['email' => $user->email]);

        $this->assertNotNull($user->fresh()->invitation);

    }
}
