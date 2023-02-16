<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TestController extends Controller
{

    public function token(Request $request)
    {
        $token = $request->user()->createToken('test-token');

        return ['token' => $token->plainTextToken];
    }

    public function resetRolesAndPermissions(Request $request)
    {

        PermissionsSeeder::resetRolesAndPermissions();
        return "DONE";

    }

//    public function mount(Request $request)
//    {
//        $url = $request->input('url');
//        $result = scandir($url);
//
//        dd($result);
//    }
}
