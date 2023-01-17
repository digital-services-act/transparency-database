<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{

    public function token(Request $request)
    {
        $token = $request->user()->createToken('test-token');

        return ['token' => $token->plainTextToken];
    }

//    public function mount(Request $request)
//    {
//        $url = $request->input('url');
//        $result = scandir($url);
//
//        dd($result);
//    }
}
