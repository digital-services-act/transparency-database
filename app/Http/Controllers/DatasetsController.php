<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DatasetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('datasets.index');
    }


}
