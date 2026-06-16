<?php

namespace Tests\Feature\Factories;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class StatementFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_statement_for_user_with_non_dsa_platform(): void
    {
        $dsaPlatform = Platform::getDsaPlatform();
        $dsaUser = User::factory()->create([
            'platform_id' => $dsaPlatform->id,
        ]);

        $statement = Statement::factory()->create();
        $statement->load('user.platform', 'platform');

        $this->assertNotSame($dsaUser->id, $statement->user_id);
        $this->assertNotNull($statement->user);
        $this->assertNotNull($statement->user->platform);
        $this->assertSame($statement->user->platform_id, $statement->platform_id);
        $this->assertSame($statement->user->platform_id, $statement->platform->id);
        $this->assertNotSame(Platform::LABEL_DSA_TEAM, $statement->user->platform->name);
    }

    public function test_it_cannot_be_used_in_production(): void
    {
        $initialCount = Statement::query()->count();

        config()->set('app.env', 'production');

        try {
            Statement::factory()->create();
            $this->fail('StatementFactory was allowed to create a statement in production.');
        } catch (RuntimeException $exception) {
            $this->assertSame('StatementFactory cannot be used in production.', $exception->getMessage());
            $this->assertSame($initialCount, Statement::query()->count());
        } finally {
            config()->set('app.env', 'testing');
        }
    }

    public function test_it_indexes_created_statement_when_elastic_is_configured_outside_production(): void
    {
        $elastic = ElasticMocker::fake()->indexReturns();

        $statement = Statement::factory()->create();

        $this->assertCount(1, $elastic->requests());

        $request = $elastic->requests()[0];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/statement_index/_doc/'.$statement->id, $request->getUri()->getPath());
        $this->assertSame('true', $query['require_alias']);
    }

    public function test_it_creates_eligible_user_when_none_exists(): void
    {
        User::query()->forceDelete();

        $statement = Statement::factory()->create();
        $statement->load('user.platform');

        $this->assertNotNull($statement->user);
        $this->assertNotNull($statement->user->platform);
        $this->assertSame($statement->user->platform_id, $statement->platform_id);
        $this->assertNotSame(Platform::LABEL_DSA_TEAM, $statement->user->platform->name);
    }

    public function test_it_creates_random_created_at_times(): void
    {
        $statements = Statement::factory()->count(25)->create();

        $times = $statements
            ->pluck('created_at')
            ->map(static fn ($createdAt): string => $createdAt->format('H:i:s'));

        $this->assertGreaterThan(1, $times->unique()->count());
        $this->assertTrue($times->contains(static fn (string $time): bool => $time !== '00:00:00'));

        foreach ($statements as $statement) {
            $this->assertSame(
                $statement->created_at->format('Y-m-d H:i:s'),
                $statement->updated_at->format('Y-m-d H:i:s'),
            );
        }
    }
}
