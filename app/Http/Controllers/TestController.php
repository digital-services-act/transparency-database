<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function mount(Request $request)
    {
        $url = $request->input('url');
        $result = scandir($url);

        dd($result);
    }
}
