<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntityStoreRequest;
use App\Models\Entity;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $entities = Entity::all();

        return view('entity.index', compact('entities'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('entity.create');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Entity $entity
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Entity $entity)
    {
        return view('entity.show', compact('entity'));
    }

    /**
     * @param \App\Http\Requests\EntityStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(EntityStoreRequest $request)
    {
        $entity = Entity::create($request->validated());

        return redirect()->route('entity.index');
    }
}
