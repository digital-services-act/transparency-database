<?php

namespace Tests\Feature\Models;


use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function saving_platform_saves_the_user(): void
    {
        $platform = Platform::all()->random()->first();
        $this->assertNull($platform->updated_by);
        $this->signInAsAdmin();
        $platform->name = $platform->name . ' updated';
        $platform->save();
        $platform->refresh();
        $this->assertNotNull($platform->updated_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function platofrm_slugify_is_working(): void
    {
        $platform = Platform::create([
            'vlop' => 1,
            'onboarded' => 1,
            'name' => 'test platform'
        ]);
        $this->assertEquals('test-platform', $platform->slugifyName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function saving_platform_saves_null_user_on_no_auth(): void
    {
        $platform = Platform::all()->random()->first();
        $this->assertNull($platform->updated_by);
        $platform->name = $platform->name . ' updated';
        $platform->save();
        $platform->refresh();
        $this->assertNull($platform->updated_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function creating_platform_saves_the_user(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::create([
            'vlop' => 1,
            'onboarded' => 1,
            'name' => 'test platform'
        ]);
        $this->assertNotNull($platform->created_by);
        $this->assertNull($platform->updated_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_tell_if_it_is_dsa_or_not(): void
    {
        $platform = Platform::create([
            'vlop' => 1,
            'onboarded' => 1,
            'name' => 'test platform'
        ]);

        $this->assertFalse($platform->isDSA());

        $platform = Platform::create([
            'vlop' => 1,
            'onboarded' => 1,
            'name' => Platform::LABEL_DSA_TEAM,
        ]);

        $this->assertTrue($platform->isDSA());
    }
}
