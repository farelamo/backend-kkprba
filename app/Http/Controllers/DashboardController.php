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

        $total = TotalViewer::select('total_views')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total->total_views ?? 0
            ],
        ]);
    }

    public function update(Request $request){

        $getTotal = TotalViewer::first();
        $total    = $getTotal->total_views ?? 0;

        $updateData = [
            'total_views' => $total + 1
        ];

        TotalViewer::updateOrCreate(
            ['id' => $getTotal->id ?? 1], $updateData
        );

        return $this->returnCondition(true, 200, 'Data updated successfully');
    }
}
