<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\departements;
use App\Models\ReservationAllowance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ReservationStaticsCreditController extends Controller
{

    public function static()
    {
        return view("reservation_statics_credit.index");
    }
    
    public function getAll()
{
    try {
        $data = [
           
        ];

        return DataTables::of($data)->make(true);
    } catch (\Exception $e) {
        \Log::error("Error fetching data: " . $e->getMessage());

        return response()->json([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Failed to load data'
        ]);
    }
}

    
}
