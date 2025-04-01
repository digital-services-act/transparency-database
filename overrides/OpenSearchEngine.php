<?php

declare(strict_types=1);

namespace Zing\LaravelScout\OpenSearch\Engines;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Jobs\RemoveableScoutCollection;
use OpenSearch\Client;

/**
 * @mixin \OpenSearch\Client
 */
class OpenSearchEngine extends Engine
{
    /**
     * Create a new engine instance.
     */
    public function __construct(
        protected Client $client,
        protected bool $softDelete = false
    ) {
    }

    /**
     * Update the given model in the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, covariant \Illuminate\Database\Eloquent\Model> $models
     */
    public function update($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $model First model for search index */
        $model = $models->first();
        if ($this->usesSoftDelete($model) && $this->softDelete) {
            $models->each->pushSoftDeleteMetadata();
        }

        $objects = $models->map(static function ($model): array {
            $searchableData = $model->toSearchableArray();
            if (empty($searchableData)) {
                return [];
            }

            return array_merge($searchableData, $model->scoutMetadata(), [
                $model->getScoutKeyName() => $model->getScoutKey(),
            ]);
        })
            ->filter()
            ->values()
            ->all();

        if ($objects !== []) {
            $data = [];
            foreach ($objects as $object) {
                $data[] = [
                    'index' => [
                        '_index' => $model->searchableAs(),
                        '_id' => $object[$model->getScoutKeyName()],
                    ],
                ];
                $data[] = $object;
            }

            $this->client->bulk([
                'index' => $model->searchableAs(),
                'body' => $data,
            ]);
        }
    }

    /**
     * Remove the given model from the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, covariant \Illuminate\Database\Eloquent\Model> $models
     */
    public function delete($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $models->first();

        $keys = $models instanceof RemoveableScoutCollection
            ? $models->pluck($model->getScoutKeyName())
            : $models->map->getScoutKey();

        $data = $keys->map(static fn($object): array => [
            'delete' => [
                '_index' => $model->searchableAs(),
                '_id' => $object,
            ],
        ])->all();

        $this->client->bulk([
            'index' => $model->searchableAs(),
            'body' => $data,
        ]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param \Laravel\Scout\Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     */
    public function search(Builder $builder): mixed
    {
        return $this->performSearch($builder, array_filter([
            'size' => $builder->limit,
        ]));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param int $perPage
     * @param int $page
     * @param \Laravel\Scout\Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     */
    public function paginate(Builder $builder, $perPage, $page): mixed
    {
        return $this->performSearch($builder, [
            'size' => $perPage,
            'from' => $perPage * ($page - 1),
        ]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param array<string, mixed> $options
     * @param \Laravel\Scout\Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     */
    protected function performSearch(Builder $builder, array $options = []): mixed
    {
        $index = $builder->index ?: $builder->model->searchableAs();
        if (property_exists($builder, 'options')) {
            $options = array_merge($builder->options, $options);
        }

        if ($builder->callback instanceof \Closure) {
            $result = \call_user_func($builder->callback, $this->client, $builder->query, $options);

            return Arr::isAssoc($result['hits'] ?? []) ? $result['hits'] : $result;
        }

        $query = $builder->query;

        /** @var \Illuminate\Support\Collection<int, array{query_string?: array{query: string, term?: array<string, mixed>}}> $must */
        $must = collect([
            [
                'query_string' => [
                    'query' => $query,
                ],
            ],
        ]);
        $must = $must->merge(collect($builder->wheres)
            ->map(static fn($value, $key): array => [
                'term' => [
                    $key => $value,
                ],
            ])->values())->values();

        if (property_exists($builder, 'whereIns')) {
            $must = $must->merge(collect($builder->whereIns)->map(static fn($values, $key): array => [
                'terms' => [
                    $key => $values,
                ],
            ])->values())->values();
        }

        $mustNot = collect();
        if (property_exists($builder, 'whereNotIns')) {
            $mustNot = $mustNot->merge(collect($builder->whereNotIns)->map(static fn($values, $key): array => [
                'terms' => [
                    $key => $values,
                ],
            ])->values())->values();
        }

        $options['query'] = [
            'bool' => [
                'must' => $must->all(),
                'must_not' => $mustNot->all(),
            ],
        ];

        $options['sort'] = collect($builder->orders)->map(static fn($order): array => [
            $order['column'] => [
                'order' => $order['direction'],
            ],
        ])->all();
        $result = $this->client->search([
            'index' => $index,
            'body' => $options,
        ]);

        return $result['hits'] ?? null;
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param array{hits: mixed[]|null}|null $results
     *
     * @return \Illuminate\Support\Collection<int, int|string>
     */
    public function mapIds($results): Collection
    {
        if ($results === null) {
            return collect();
        }

        return collect($results['hits'])->pluck('_id')->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param array{hits: mixed[]|null}|null $results
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Laravel\Scout\Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function map(Builder $builder, $results, $model): mixed
    {
        if ($results === null) {
            return $model->newCollection();
        }

        if (!isset($results['hits'])) {
            return $model->newCollection();
        }

        if ($results['hits'] === []) {
            return $model->newCollection();
        }

        $objectIds = collect($results['hits'])->pluck('_id')->values()->all();

        $objectIdPositions = array_flip($objectIds);

        return $model->getScoutModelsByIds($builder, $objectIds)
            ->filter(static fn($model): bool => \in_array($model->id, $objectIds, false))
            ->sortBy(static fn($model): int => $objectIdPositions[$model->id])->values();
    }

    /**
     * Map the given results to instances of the given model via a lazy collection.
     *
     * @param array{hits: mixed[]|null}|null $results
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Laravel\Scout\Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     *
     * @return \Illuminate\Support\LazyCollection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function lazyMap(Builder $builder, $results, $model): LazyCollection
    {
        if ($results === null) {
            return LazyCollection::make($model->newCollection());
        }

        if (!isset($results['hits'])) {
            return LazyCollection::make($model->newCollection());
        }

        if ($results['hits'] === []) {
            return LazyCollection::make($model->newCollection());
        }

        $objectIds = collect($results['hits'])->pluck('_id')->values()->all();
        $objectIdPositions = array_flip($objectIds);

        return $model->queryScoutModelsByIds($builder, $objectIds)
            ->cursor()
            ->filter(static fn($model): bool => \in_array($model->getScoutKey(), $objectIds, false))
            ->sortBy(static fn($model): int => $objectIdPositions[$model->getScoutKey()])->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param mixed $results
     */
    public function getTotalCount($results): int
    {
        return $results['total']['value'] ?? 0;
    }

    /**
     * Flush all of the model's records from the engine.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function flush($model): void
    {
        $this->client->deleteByQuery([
            'index' => $model->searchableAs(),
            'body' => [
                'query' => [
                    'match_all' => new \stdClass(),
                ],
            ],
        ]);
    }

    /**
     * Create a search index.
     *
     * @param string $name
     * @param array<string, mixed> $options
     *
     * @return array{acknowledged: bool, shards_acknowledged: bool, index: string}
     *
     * @phpstan-return array<string, mixed>
     */
    public function createIndex($name, array $options = []): array
    {
        return $this->client->indices()
            ->create([
                'index' => $name,
                'body' => $options,
            ]);
    }

    /**
     * Delete a search index.
     *
     * @param string $name
     *
     * @return array{acknowledged: bool}
     *
     * @phpstan-return array<string, mixed>
     */
    public function deleteIndex($name): array
    {
        return $this->client->indices()
            ->delete([
                'index' => $name,
            ]);
    }

    /**
     * Determine if the given model uses soft deletes.
     */
    protected function usesSoftDelete(Model $model): bool
    {
        return \in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    /**
     * Dynamically call the OpenSearch client instance.
     *
     * @param string $method
     * @param array<int, mixed> $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->client->{$method}(...$parameters);
    }
}
