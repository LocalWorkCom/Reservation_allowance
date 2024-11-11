<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use App\Models\ReservationAllowance;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class SubDepartmentStatsController extends Controller
{
    public function index(Request $request, $department_id)
{
    if (auth()->check() && auth()->user()->rule_id == 2) {
        $mainDepartment = departements::find($department_id);
        $month = $request->input('month');
        $year = $request->input('year');

        return view('reservation_subdeparts.index', [
            'department_id' => $department_id,
            'main_department_name' => $mainDepartment ? $mainDepartment->name : 'Unknown Department',
            'month' => $month,
            'year' => $year
        ]);
    } else {
        return abort(403, 'Unauthorized action.');
    }
}


public function getAll(Request $request, $department_id)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $query = departements::where('parent_id', $department_id)
        ->withCount('children');

    return DataTables::of($query)
        ->addColumn('department_name', function($row) {
            return $row->name;
        })
        ->addColumn('reservation_allowance_budget', function($row) {
            return $row->reservation_allowance_amount;
        })
        ->addColumn('registered_by', function($row) use ($month, $year) {
            return ReservationAllowance::where('departement_id', $row->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');
        })
        ->addColumn('remaining_amount', function($row) use ($month, $year) {
            $budget = $row->reservation_allowance_amount;
            $registeredAmount = ReservationAllowance::where('departement_id', $row->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');
            return $budget - $registeredAmount;
        })
        ->addColumn('number_of_employees', function($row) {
            return User::where('department_id', $row->id)->where('flag', 'employee')->count();
        })
        ->addColumn('received_allowance_count', function($row) use ($month, $year) {
            return ReservationAllowance::where('departement_id', $row->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->distinct('user_id')
                ->count('user_id');
        })
        ->addColumn('did_not_receive_allowance_count', function($row) use ($month, $year) {
            $employees = User::where('department_id', $row->id)->where('flag', 'employee')->count();
            $receivedAllowance = ReservationAllowance::where('departement_id', $row->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->distinct('user_id')
                ->count('user_id');
            return $employees - $receivedAllowance;
        })
        ->make(true);
}

}
