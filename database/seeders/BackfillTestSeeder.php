<?php

namespace Database\Seeders;

use App\Models\Statement;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class BackfillTestSeeder extends Seeder
{
    private const PASSWORD_HASH = '$2y$12$/mgR2JM6SnoZCpruVMmB5Oc63sgj5TUAXq5Qp5Azq5prndbuzdpHC';

    private const DSA_PLATFORM_ID = 1;

    private const SERVICE_USER_ID = 9001;

    public function run(): void
    {
        $this->resetTables();
        $this->seedPlatforms();
        $this->seedUsers();
        $this->seedRolesAndPermissions();
        $this->seedStatements(
            (int) env('BACKFILL_FIXTURE_STATEMENT_START_ID', 2000),
            (int) env('BACKFILL_FIXTURE_STATEMENT_COUNT', 1000),
        );
        $this->seedKnownToken();
    }

    private function resetTables(): void
    {
        foreach ([
            'personal_access_tokens',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'roles',
            'permissions',
            'jobs',
            'failed_jobs',
            'job_batches',
            'users',
            'platforms',
            'statements_beta',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    private function seedPlatforms(): void
    {
        $timestamp = '2026-01-01 09:00:00';

        DB::table('platforms')->insert([
            [
                'id' => self::DSA_PLATFORM_ID,
                'name' => 'DSA Team',
                'uuid' => '10000000-0000-4000-8000-000000000001',
                'vlop' => 1,
                'dsa_common_id' => 'DSA-TEAM',
                'onboarded' => 1,
                'created_by' => null,
                'updated_by' => null,
                'has_tokens' => 1,
                'has_statements' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'id' => 101,
                'name' => 'Northstar Social',
                'uuid' => '10000000-0000-4000-8000-000000000101',
                'vlop' => 0,
                'dsa_common_id' => 'PLT-101',
                'onboarded' => 1,
                'created_by' => null,
                'updated_by' => null,
                'has_tokens' => 1,
                'has_statements' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'id' => 102,
                'name' => 'Harbor Market',
                'uuid' => '10000000-0000-4000-8000-000000000102',
                'vlop' => 0,
                'dsa_common_id' => 'PLT-102',
                'onboarded' => 1,
                'created_by' => null,
                'updated_by' => null,
                'has_tokens' => 1,
                'has_statements' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'id' => 103,
                'name' => 'Lumen Video',
                'uuid' => '10000000-0000-4000-8000-000000000103',
                'vlop' => 0,
                'dsa_common_id' => 'PLT-103',
                'onboarded' => 1,
                'created_by' => null,
                'updated_by' => null,
                'has_tokens' => 1,
                'has_statements' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'id' => 104,
                'name' => 'Pine Forum',
                'uuid' => '10000000-0000-4000-8000-000000000104',
                'vlop' => 0,
                'dsa_common_id' => 'PLT-104',
                'onboarded' => 1,
                'created_by' => null,
                'updated_by' => null,
                'has_tokens' => 1,
                'has_statements' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'id' => 105,
                'name' => 'Atlas Exchange',
                'uuid' => '10000000-0000-4000-8000-000000000105',
                'vlop' => 0,
                'dsa_common_id' => 'PLT-105',
                'onboarded' => 1,
                'created_by' => null,
                'updated_by' => null,
                'has_tokens' => 1,
                'has_statements' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
        ]);
    }

    private function seedUsers(): void
    {
        $timestamp = '2026-01-01 09:05:00';
        $adminEmail = $this->adminEmail();

        $rows = array_map(function (array $user) use ($timestamp): array {
            return [
                'id' => $user['id'],
                'email' => $user['email'],
                'email_verified_at' => $timestamp,
                'password' => self::PASSWORD_HASH,
                'name' => $user['name'],
                'platform_id' => $user['platform_id'],
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ];
        }, $this->sampleUsers());

        $rows[] = [
            'id' => self::SERVICE_USER_ID,
            'email' => $adminEmail,
            'email_verified_at' => $timestamp,
            'password' => self::PASSWORD_HASH,
            'name' => 'Backfill Admin',
            'platform_id' => self::DSA_PLATFORM_ID,
            'remember_token' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'deleted_at' => null,
        ];

        DB::table('users')->insert($rows);
    }

    private function seedRolesAndPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        PermissionsSeeder::resetRolesAndPermissions();
        (new OnboardingPermissionsSeeder())->run();
        (new ResearchPermissionsSeeder())->run();
        (new SupportPermissionsSeeder())->run();

        foreach ($this->sampleUsers() as $user) {
            User::query()->findOrFail($user['id'])->assignRole($user['role']);
        }
    }

    private function seedStatements(int $startId, int $count): void
    {
        $sampleUsers = array_values(array_map(
            static fn (array $user): array => [
                'id' => $user['id'],
                'platform_id' => $user['platform_id'],
            ],
            $this->sampleUsers()
        ));

        $decisionVisibilityKeys = array_keys(Statement::DECISION_VISIBILITIES);
        $decisionMonetaryKeys = array_keys(Statement::DECISION_MONETARIES);
        $decisionProvisionKeys = array_keys(Statement::DECISION_PROVISIONS);
        $decisionAccountKeys = array_keys(Statement::DECISION_ACCOUNTS);
        $accountTypeKeys = array_keys(Statement::ACCOUNT_TYPES);
        $decisionGroundKeys = array_keys(Statement::DECISION_GROUNDS);
        $categoryKeys = array_keys(Statement::STATEMENT_CATEGORIES);
        $keywordKeys = array_keys(Statement::KEYWORDS);
        $contentTypeKeys = array_keys(Statement::CONTENT_TYPES);
        $sourceTypeKeys = array_keys(Statement::SOURCE_TYPES);
        $automatedDecisionKeys = array_keys(Statement::AUTOMATED_DECISIONS);
        $territories = ['BE', 'FR', 'DE', 'NL', 'IT', 'ES', 'PL', 'SE'];
        $languages = ['en', 'fr', 'de', 'it', 'es'];
        $methods = [Statement::METHOD_API, Statement::METHOD_FORM];

        $rows = [];

        for ($offset = 0; $offset < $count; $offset++) {
            $id = $startId + $offset;
            $user = $sampleUsers[$offset % count($sampleUsers)];
            $createdAt = CarbonImmutable::create(2026, 1, 1, 8, 0, 0)->addMinutes($id);
            $decisionGround = $decisionGroundKeys[$offset % count($decisionGroundKeys)];
            $illegal = $decisionGround === 'DECISION_GROUND_ILLEGAL_CONTENT';

            $rows[] = [
                'id' => $id,
                'uuid' => $this->uuidForId($id),
                'decision_visibility' => $this->jsonValue($this->pairFrom($decisionVisibilityKeys, $offset)),
                'decision_visibility_other' => sprintf('visibility-other-%d', $id),
                'decision_monetary' => $decisionMonetaryKeys[$offset % count($decisionMonetaryKeys)],
                'decision_monetary_other' => sprintf('monetary-other-%d', $id),
                'decision_provision' => $decisionProvisionKeys[$offset % count($decisionProvisionKeys)],
                'decision_account' => $decisionAccountKeys[$offset % count($decisionAccountKeys)],
                'account_type' => $accountTypeKeys[$offset % count($accountTypeKeys)],
                'decision_ground' => $decisionGround,
                'decision_ground_reference_url' => sprintf('https://example.test/grounds/%d', $id),
                'category' => $categoryKeys[$offset % count($categoryKeys)],
                'category_addition' => $this->jsonValue($this->pairFrom($categoryKeys, $offset + 1)),
                'category_specification' => $this->jsonValue($this->pairFrom($keywordKeys, $offset + 2)),
                'category_specification_other' => sprintf('keyword-other-%d', $id),
                'content_type' => $this->jsonValue($this->pairFrom($contentTypeKeys, $offset + 3)),
                'content_type_other' => sprintf('content-type-other-%d', $id),
                'illegal_content_legal_ground' => $illegal ? sprintf('Article %d', 10 + ($offset % 9)) : null,
                'illegal_content_explanation' => $illegal ? sprintf('Illegal content explanation for statement %d', $id) : null,
                'incompatible_content_ground' => $illegal ? null : sprintf('Terms section %d', 20 + ($offset % 7)),
                'incompatible_content_explanation' => $illegal ? null : sprintf('Terms explanation for statement %d', $id),
                'incompatible_content_illegal' => $illegal ? null : Statement::INCOMPATIBLE_CONTENT_ILLEGAL_NO,
                'territorial_scope' => $this->jsonValue($this->pairFrom($territories, $offset + 4)),
                'content_language' => $languages[$offset % count($languages)],
                'content_date' => $createdAt->subDays(2)->format('Y-m-d H:i:s'),
                'application_date' => $createdAt->subDay()->format('Y-m-d H:i:s'),
                'end_date_visibility_restriction' => $createdAt->addDays(10)->format('Y-m-d H:i:s'),
                'end_date_monetary_restriction' => $createdAt->addDays(11)->format('Y-m-d H:i:s'),
                'end_date_service_restriction' => $createdAt->addDays(12)->format('Y-m-d H:i:s'),
                'end_date_account_restriction' => $createdAt->addDays(13)->format('Y-m-d H:i:s'),
                'decision_facts' => sprintf('Backfill test statement %d with deterministic fixture data.', $id),
                'source_type' => $sourceTypeKeys[$offset % count($sourceTypeKeys)],
                'source_identity' => sprintf('source-%d', $id),
                'automated_detection' => Statement::AUTOMATED_DETECTIONS[$offset % count(Statement::AUTOMATED_DETECTIONS)],
                'automated_decision' => $automatedDecisionKeys[$offset % count($automatedDecisionKeys)],
                'user_id' => $user['id'],
                'platform_id' => $user['platform_id'],
                'method' => $methods[$offset % count($methods)],
                'puid' => sprintf('platform-%d-user-%d-statement-%d', $user['platform_id'], $user['id'], $id),
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
                'deleted_at' => null,
                'content_id_ean' => $offset % 3 === 0 ? sprintf('%013d', 4000000000000 + $id) : null,
            ];

            if (count($rows) === 100) {
                DB::table('statements_beta')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('statements_beta')->insert($rows);
        }
    }

    private function seedKnownToken(): void
    {
        $plainTextToken = trim((string) env('BACKFILL_TOKEN', ''));

        if ($plainTextToken === '') {
            return;
        }

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => User::class,
            'tokenable_id' => self::SERVICE_USER_ID,
            'name' => 'backfill-test',
            'token' => hash('sha256', $plainTextToken),
            'abilities' => json_encode(['*']),
            'last_used_at' => null,
            'expires_at' => null,
            'created_at' => '2026-01-01 09:10:00',
            'updated_at' => '2026-01-01 09:10:00',
        ]);
    }

    /**
     * @return array<int, array{id:int,name:string,email:string,platform_id:int,role:string}>
     */
    private function sampleUsers(): array
    {
        return [
            ['id' => 1001, 'name' => 'Avery Hart', 'email' => 'avery.hart@northstar.test', 'platform_id' => 101, 'role' => 'Contributor'],
            ['id' => 1002, 'name' => 'Milo Quinn', 'email' => 'milo.quinn@northstar.test', 'platform_id' => 101, 'role' => 'User'],
            ['id' => 1003, 'name' => 'Nina Vale', 'email' => 'nina.vale@harbor.test', 'platform_id' => 102, 'role' => 'Contributor'],
            ['id' => 1004, 'name' => 'Owen Pike', 'email' => 'owen.pike@harbor.test', 'platform_id' => 102, 'role' => 'User'],
            ['id' => 1005, 'name' => 'Iris Lane', 'email' => 'iris.lane@lumen.test', 'platform_id' => 103, 'role' => 'Contributor'],
            ['id' => 1006, 'name' => 'Theo Marsh', 'email' => 'theo.marsh@lumen.test', 'platform_id' => 103, 'role' => 'User'],
            ['id' => 1007, 'name' => 'Ruby Frost', 'email' => 'ruby.frost@pine.test', 'platform_id' => 104, 'role' => 'Contributor'],
            ['id' => 1008, 'name' => 'Eli Stone', 'email' => 'eli.stone@pine.test', 'platform_id' => 104, 'role' => 'User'],
            ['id' => 1009, 'name' => 'Zoe Mercer', 'email' => 'zoe.mercer@atlas.test', 'platform_id' => 105, 'role' => 'Contributor'],
            ['id' => 1010, 'name' => 'Noah Reed', 'email' => 'noah.reed@atlas.test', 'platform_id' => 105, 'role' => 'User'],
        ];
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private function pairFrom(array $values, int $offset): array
    {
        $first = $values[$offset % count($values)];
        $second = $values[($offset + 3) % count($values)];

        if ($first === $second) {
            $second = $values[($offset + 1) % count($values)];
        }

        return [$first, $second];
    }

    /**
     * @param  array<int, string>  $value
     */
    private function jsonValue(array $value): string
    {
        return (string) json_encode(array_values($value));
    }

    private function uuidForId(int $id): string
    {
        return sprintf('00000000-0000-4000-8000-%012d', $id);
    }

    private function adminEmail(): string
    {
        foreach (explode(',', (string) config('dsa.ADMIN_EMAILS')) as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return 'backfill-admin@clever.test';
    }
}
