<?php

namespace App\Http\Controllers;

use App\Models\Statement;
use Illuminate\Http\Request;

class TestSearchController extends Controller
{
    public function index()
    {
         $text_to_search = "dreams";

         $result = Statement::search($text_to_search)->orderBy('created_at');

         return $result->raw();
    }
}
