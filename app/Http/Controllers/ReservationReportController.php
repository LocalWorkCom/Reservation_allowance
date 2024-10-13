<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use App\Models\ReservationAllowance;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ReservationReportController extends Controller
{
    public function index()
    {
        return view('reserv_report.index');
    }

   public function getReportData(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Convert dates to Carbon instances
    $start = Carbon::parse($startDate)->startOfDay();
    $end = Carbon::parse($endDate)->endOfDay();

    // Query to group by department and calculate totals
    $query = ReservationAllowance::whereBetween('date', [$start, $end])
        ->selectRaw('departement_id, SUM(amount) as total_amount, COUNT(DISTINCT user_id) as user_count')
        ->groupBy('departement_id');

    $data = DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('department_name', function ($row) {
            return departements::find($row->departement_id)->name ?? 'N/A';
        })
        ->make(true);

    // Calculating distinct department count and other totals
    $totalDepartments = $query->distinct('departement_id')->count('departement_id');  // Ensuring unique departments
    $totalUsers = $query->get()->sum('user_count');
    $totalAmount = number_format($query->get()->sum('total_amount'), 2);

    // Adding totals to the JSON response
    $response = $data->getData(true);
    $response['totalDepartments'] = $totalDepartments;
    $response['totalUsers'] = $totalUsers;
    $response['totalAmount'] = $totalAmount;

    return response()->json($response);
}

    
}
