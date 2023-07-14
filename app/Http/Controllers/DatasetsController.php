<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DatasetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $excel  = Storage::disk('s3')->url('statements.xlsx');
        $csv =  Storage::disk('s3')->url('statements.csv');
        return view ('datasets.index', [
            "excel" => $excel,
            "csv" => $csv
        ]);
    }


}
