<?php

namespace App\Http\Controllers\Api\v1;

use \Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BackfillAPIController extends Controller
{
    // This is the first id to start with -1 as the first statement in the old database has id 102107966886
    private $start_id = 102107966885;
    // This is the absolute highest to go to this number will change when we go live. 
    // For now this is starting id of the new database to avoid any conflicts with the old database.
    private $end_id = 200000000000;
    private $table = 'statements_beta';

    public function __construct()
    {
        
    }

    public function statements(Request $request)
    {
        $bulkInsertData = $request->input('statements');
        // Insert the data into the database
        DB::table($this->table)->insert($bulkInsertData);

        return response()->json([
            'message' => 'ok',
        ]);
    }

    public function highestImportedId()
    {
        $lowestId = DB::table($this->table)
            ->where('id', '<', $this->end_id)
            ->where('id', '>', $this->start_id)
            ->max('id');
        if (!$lowestId) {
            $lowestId = $this->start_id;
        }    
        return response()->json([
            'lowest_id' => $lowestId,
        ]);
    }
}