<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\EntityController
 */

uses(AdditionalAssertions::class);
uses(RefreshDatabase::class);
uses(WithFaker::class);



it('should display view', function () {
    $entities = Entity::factory()->count(3)->create();

    $response = $this->get(route('entity.index'));

    $response->assertOk();
    $response->assertViewIs('entity.index');
    $response->assertViewHas('entities');
});

it('should display create view', function () {
    $response = $this->get(route('entity.create'));

    $response->assertOk();
    $response->assertViewIs('entity.create');
});


it('should display show view', function () {
    $entity = Entity::factory()->create();

    $response = $this->get(route('entity.show', $entity));

    $response->assertOk();
    $response->assertViewIs('entity.show');
    $response->assertViewHas('entity');
});

it('should use form request validation', function () {
    $this->assertActionUsesFormRequest(
        \App\Http\Controllers\EntityController::class,
        'store',
        \App\Http\Requests\EntityStoreRequest::class
    );
});


it('should save and redirect', function () {
    $name = $this->faker->name;
    $kind = $this->faker->randomElement(['individual', 'organization']);

    $response = $this->post(route('entity.store'), [
        'name' => $name,
        'kind' => $kind,
    ]);

    $entities = Entity::query()
        ->where('name', $name)
        ->where('kind', $kind)
        ->get();
    $this->assertCount(1, $entities);
    $entity = $entities->first();

    $response->assertRedirect(route('entity.index'));
});


