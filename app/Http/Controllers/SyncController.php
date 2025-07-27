<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function pull(Request $request)
    {
        $table = $request->input('table');
        $since = $request->input('since');

        if (!in_array($table, config('sync.pull_from_remote'))) {
            return response()->json(['error' => 'Unauthorized table'], 403);
        }

        try {
            $data = DB::table($table)
                ->where('updated_at', '>=', $since)
                ->get();
        } catch (\Exception $e) {
            Log::info($table);
        }

        return response()->json(['data' => $data]);
    }
}
