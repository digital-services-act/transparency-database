<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Controllers\Traits\StatementAPITrait;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use ClickHouseDB\Client;
use ClickHouseDB\Quote\FormatLine;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;


class StatementCHAPIController extends Controller
{
    use Sanitizer;
    use ExceptionHandlingTrait;
    use StatementAPITrait;



    public function __construct(
        protected Client $client,
        protected EuropeanCountriesService $european_countries_service
    ) {
    }

    public function show(string $uuid): mixed
    {
        $uuid = preg_replace('/[^a-zA-Z0-9-]/', '', $uuid);

        if (strlen($uuid) !== 36) {
            return response()->json(['message' => 'Invalid UUID'], Response::HTTP_BAD_REQUEST);
        }
        $uuid = "'" . $uuid . "'";

        $statement = $this->client->select('SELECT * FROM statements WHERE uuid = ' . $uuid)->rows()[0] ?? null;
        if (!$statement) {
            return response()->json(['message' => 'statement of reason not found'], Response::HTTP_NOT_FOUND);
        }

        unset($statement['user_id']);
        $statement['self'] = route('api.v2.chstatement.show', ['uuid' => $statement['uuid']]);

        $platforms = Platform::all()->pluck('name', 'id')->toArray();
        $statement['platform_name'] = $platforms[$statement['platform_id']] ?? null;

        return $statement;
    }

    private function existingStatementFromPlatformIdAndPuid(int $platform_id, string $puid): mixed
    {
        $puid = preg_replace('/[^a-zA-Z0-9-]/', '', $puid);
        $puid = "'" . $puid . "'";
        $sql = 'SELECT * FROM statements WHERE platform_id = ' . $platform_id . ' AND puid = ' . $puid;

        $statement = $this->client->select($sql)->rows()[0] ?? null;
        if (!$statement) {
            return null;
        }
        return $statement;
    }

    public function existingPuid(Request $request, string $puid): mixed
    {
        $puid = preg_replace('/[^a-zA-Z0-9-]/', '', $puid);
        $statement = $this->existingStatementFromPlatformIdAndPuid($request->user()->platform_id, $puid);
        if (!$statement) {
            return response()->json(['message' => 'statement of reason not found'], Response::HTTP_NOT_FOUND);
        }
        return $statement;
    }

    public function store(StatementStoreRequest $request): mixed
    {
        $validated = $request->validated();

        // $existing_statement = $this->existingStatementFromPlatformIdAndPuid($request->user()->platform_id, $validated['puid']);
        // if ($existing_statement) {
        //     return response()->json(['message' => 'statement of reason already exists'], Response::HTTP_CONFLICT);
        // }

        $validated['platform_id'] = $request->user()->platform_id;
        $validated['uuid'] = Str::uuid()->toString();
        $validated['created_at'] = date('Y-m-d H:i:s');
        $validated['user_id'] = $request->user()->id;
        $validated['method'] = Statement::METHOD_API;

        $validated['territorial_scope'] = $this->european_countries_service->filterSortEuropeanCountries($validated['territorial_scope'] ?? []);

        $validated['content_type'] = array_unique($validated['content_type']);
        sort($validated['content_type']);

        if (array_key_exists('decision_visibility', $validated) && !is_null($validated['decision_visibility'])) {
            $validated['decision_visibility'] = array_unique($validated['decision_visibility']);
            sort($validated['decision_visibility']);
        } else {
            $validated['decision_visibility'] = [];
        }

        if (array_key_exists('category_specification', $validated) && !is_null($validated['category_specification'])) {
            $validated['category_specification'] = array_unique($validated['category_specification']);
            sort($validated['category_specification']);
        } else {
            $validated['category_specification'] = [];
        }

        if (array_key_exists('category_addition', $validated) && !is_null($validated['category_addition'])) {
            $validated['category_addition'] = array_unique($validated['category_addition']);
            sort($validated['category_addition']);
        } else {
            $validated['category_addition'] = [];
        }

        $validated['automated_detection'] = ($validated['automated_detection'] ?? 'No') === 'Yes' ? 1 : 0;
        $validated['incompatible_content_illegal'] = ($validated['incompatible_content_illegal'] ?? 'No') === 'Yes' ? 1 : 0;

        $columns = array_keys($validated);
        $values = array_values($validated);

        $sql = 'INSERT INTO statements';
        if (count($columns) !== 0) {
            $sql .= ' (`' . implode('`,`', $columns) . '`) ';
        }

        $sql .= ' SETTINGS async_insert=1, wait_for_async_insert=0 VALUES ';
        $sql .= '(' . FormatLine::Insert($values) . ')';



        try {
            $this->client->write($sql);
        } catch (Exception $e) {
            return response()->json(['message' => 'clickhouse-insert: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $validated['self'] = route('api.v2.chstatement.show', ['uuid' => $validated['uuid']]);

        return response()->json($validated, Response::HTTP_CREATED);
    }
}
