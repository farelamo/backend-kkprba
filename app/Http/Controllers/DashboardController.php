<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TotalViewer;

class DashboardController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api')->except(['update']);
    }

    public function index(){

        $day   = TotalViewer::where('date', date('Y-m-d'))->count();
        $month = TotalViewer::where('date', 'like', '%' . date('Y-m') . '%')->count();
        $year  = TotalViewer::where('date', 'like', '%' . date('Y') . '%')->count();
        $total = TotalViewer::count();

        return response()->json([
            'success' => true,
            'data' => [
                'day'   => $day,
                'month' => $month,
                'year'  => $year,
                'total' => $total
            ],
        ]);
    }

    public function update(Request $request){

        $now = date('Y-m-d');

        TotalViewer::create([
            'date' => $now
        ]);

        return $this->returnCondition(true, 200, 'Data created successfully');
    }
}
