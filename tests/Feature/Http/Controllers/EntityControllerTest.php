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
class EntityControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function index_displays_view()
    {
        $entities = Entity::factory()->count(3)->create();

        $response = $this->get(route('entity.index'));

        $response->assertOk();
        $response->assertViewIs('entity.index');
        $response->assertViewHas('entities');
    }


    /**
     * @test
     */
    public function create_displays_view()
    {
        $response = $this->get(route('entity.create'));

        $response->assertOk();
        $response->assertViewIs('entity.create');
    }


    /**
     * @test
     */
    public function show_displays_view()
    {
        $entity = Entity::factory()->create();

        $response = $this->get(route('entity.show', $entity));

        $response->assertOk();
        $response->assertViewIs('entity.show');
        $response->assertViewHas('entity');
    }


    /**
     * @test
     */
    public function store_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\EntityController::class,
            'store',
            \App\Http\Requests\EntityStoreRequest::class
        );
    }

    /**
     * @test
     */
    public function store_saves_and_redirects()
    {
        $name = $this->faker->name;
        $kind = $this->faker->randomElement(['individual','organization']);

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
    }
}
