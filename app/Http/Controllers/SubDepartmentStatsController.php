<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use App\Models\ReservationAllowance;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class SubDepartmentStatsController extends Controller
{
    public function index($department_id)
    {
        // Fetch the main department details
        $mainDepartment = departements::find($department_id);

        return view('reservation_subdeparts.index', [
            'department_id' => $department_id,
            'main_department_name' => $mainDepartment ? $mainDepartment->name : 'Unknown Department',
        ]);
    }

    public function getAll($department_id)
    {
        $query = departements::where('parent_id', $department_id)
            ->withCount('children');  // Count nested sub-departments, if any

        return DataTables::of($query)
            ->addColumn('department_name', function($row) {
                return $row->name;
            })
            ->addColumn('reservation_allowance_budget', function($row) {
                return $row->reservation_allowance_amount;
            })
            ->addColumn('registered_by', function($row) {
                return ReservationAllowance::where('departement_id', $row->id)->sum('amount');
            })
            ->addColumn('remaining_amount', function($row) {
                $budget = $row->reservation_allowance_amount;
                $registeredAmount = ReservationAllowance::where('departement_id', $row->id)->sum('amount');
                return $budget - $registeredAmount;
            })
            ->addColumn('number_of_employees', function($row) {
                return User::where('department_id', $row->id)->where('flag', 'employee')->count();
            })
            ->addColumn('received_allowance_count', function($row) {
                return ReservationAllowance::where('departement_id', $row->id)->distinct('user_id')->count('user_id');
            })
            ->addColumn('did_not_receive_allowance_count', function($row) {
                $employees = User::where('department_id', $row->id)->where('flag', 'employee')->count();
                $receivedAllowance = ReservationAllowance::where('departement_id', $row->id)->distinct('user_id')->count('user_id');
                return $employees - $receivedAllowance;
            })
            ->make(true);
    }
}
