<?php

namespace Tests\Feature\Services;

use App\Models\ArchivedStatement;
use App\Models\Statement;
use App\Services\StatementArchiveService;
use App\Services\StatementStoreLockingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementStoreLockingServiceTest extends TestCase
{

    use RefreshDatabase;

    public StatementStoreLockingService $statement_store_locking_service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statement_store_locking_service = app(StatementStoreLockingService::class);
        $this->assertNotNull($this->statement_store_locking_service);
    }

    /**
     * @test
     * @return void
     */
    public function it_locks_on_a_single_puid(): void
    {
        $platform_id = 26;
        $puids = [
            'tk421'
        ];

        $first_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids);
        $second_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids);

        $this->assertTrue($first_attempt);
        $this->assertFalse($second_attempt);

    }

    /**
     * @test
     * @return void
     */
    public function it_lets_the_locks_go(): void
    {
        $platform_id = 26;
        $puids = [
            'tk421'
        ];

        $this->statement_store_locking_service->wait = 2; // seconds

        $first_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids);
        sleep(3);
        $second_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids);

        $this->assertTrue($first_attempt);
        $this->assertTrue($second_attempt);

    }

    /**
     * @test
     * @return void
     */
    public function it_locks_on_a_multi_puid(): void
    {
        $platform_id = 26;
        $puids_set_one = [
            'tk421',
            'thx1138',
            '65465465',
            '7894613',
            '1345679',
            '123456789',
            '456789123',
            '456123789',
        ];

        $puids_set_two = [
            'thx1138thx1138',
            '6546546565465465',
            '78946137894613',
            '13456791345679',
            '123456789123456789',
            '456789123456789123',
            '456123789456123789',
            'tk421' // this one will set it off.
        ];

        $first_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_one);
        $second_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_two);

        $this->assertTrue($first_attempt);
        $this->assertFalse($second_attempt);
    }

    /**
     * @test
     * @return void
     */
    public function it_lets_the_locks_go_on_a_multi_puid(): void
    {
        $platform_id = 26;
        $puids_set_one = [
            'tk421',
            'thx1138',
            '65465465',
            '7894613',
            '1345679',
            '123456789',
            '456789123',
            '456123789',
        ];

        $puids_set_two = [
            'thx1138thx1138',
            '6546546565465465',
            '78946137894613',
            '13456791345679',
            '123456789123456789',
            '456789123456789123',
            '456123789456123789',
            'tk421' // this one will set it off.
        ];

        $this->statement_store_locking_service->wait = 2;
        $first_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_one);
        sleep(3);
        $second_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_two);

        $this->assertTrue($first_attempt);
        $this->assertTrue($second_attempt);

        $first_attempt_repeated = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_one);

        // we didn't wait long enough.
        $this->assertFalse($first_attempt_repeated);
    }

    /**
     * @test
     * @return void
     */
    public function it_locks_one_hundred_and_blocks(): void
    {
        $platform_id = 26;
        $puids_set_one = range(0, 99);
        $puids_set_two = range(100, 199);
        $puids_set_three = range(200, 299);
        $puids_set_three[77] = 188; // repeat a puid from the second set
        $puids_set_three[82] = 83; // repeat a puid. from the first set

        $first_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_one);
        $second_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_two);
        $third_attempt = $this->statement_store_locking_service->getTheLocksFor($platform_id, $puids_set_three);

        $this->assertTrue($first_attempt);
        $this->assertTrue($second_attempt);
        $this->assertFalse($third_attempt);
    }
}
