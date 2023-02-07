<?php

namespace App\Http\Controllers;

use App\Models\Statement;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {

        $query = $request->get('query');

        $results =  Statement::search($query)->paginate(10);

        return view('search.results', compact('results','query'));


    }
}
