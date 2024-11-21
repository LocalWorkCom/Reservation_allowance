<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use App\Models\ReservationAllowance;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;


class SubDepartmentStatsController extends Controller
{
    public function index($departmentId, Request $request)
    {
        $department = departements::find($departmentId);
        return view('reservation_subdeparts.index', [
            'departmentId' => $departmentId,
            'departmentName' => $department ? $department->name : 'Unknown Department',
            'month' => $request->query('month'),
            'year' => $request->query('year'),
        ]);
    }

    public function getAll($departmentId, Request $request)
    {
        try {
            $month = $request->query('month');
            $year = $request->query('year');
            if (!$month || !$year) {
                return response()->json([
                    'error' => 'Please select both month and year.'
                ], 400);
            }
    
            $query = departements::where('parent_id', $departmentId);
    
            return DataTables::of($query)
                ->addColumn('sub_department_name',fn($row) => $row->name 
                    )
                ->addColumn('reservation_allowance_budget', function ($row) use ($month, $year) {
                    $amount = DB::table('history_allawonces')
                        ->where('department_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->sum('amount');
    
                    if (is_null($amount) || $amount == 0) {
                        return "ميزانية غير محدده";
                    }
                    return number_format($amount, 2) . " د.ك";
                })
                ->addColumn('registered_by', function ($row) use ($month, $year) {
                    $sum = ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->sum('amount');
    
                    return number_format($sum, 2) . " د.ك";
                })
                ->addColumn('remaining_amount', function ($row) use ($month, $year) {
                    $registeredAmount = ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->sum('amount');
    
                    $historicalAmount = DB::table('history_allawonces')
                        ->where('department_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->value('amount');
    
                    if (is_null($historicalAmount) || $historicalAmount == 0) {
                        return "-";
                    }
    
                    $remainingAmount = $historicalAmount - $registeredAmount;
                    return number_format($remainingAmount, 2) . " د.ك";
                })
                ->addColumn('employees_count', function ($row) {
                    return User::where('department_id', $row->id)->count();
                })
                ->addColumn('received_allowance_count', function ($row) use ($month, $year) {
                    return ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                })
                ->addColumn('did_not_receive_allowance_count', function ($row) use ($month, $year) {
                    $employeesCount = User::where('department_id', $row->id)->count();
                    $receivedAllowanceCount = ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
    
                    return $employeesCount - $receivedAllowanceCount;
                })
                ->make(true);
        } catch (\Exception $e) {
            \Log::error("Error fetching sub-departments for department ID $departmentId: " . $e->getMessage());
    
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load sub-department data. Please try again later.'
            ]);
        }
    }

    public function subDepartmentEmployeesPage(Request $request, $subDepartmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');
    $subDepartment = departements::find($subDepartmentId);

    return view('reservation_subdeparts.subdeparts_employee', [
        'subDepartmentId' => $subDepartmentId,
        'subDepartmentName' => $subDepartment->name ?? 'Unknown Subdepartment',
        'month' => $month,
        'year' => $year,
    ]);
}

public function getSubDepartmentEmployees(Request $request, $subDepartmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $users = User::where('department_id', $subDepartmentId)
        ->with(['grade'])
        ->get();

    return DataTables::of($users)
        ->addColumn('file_number', fn($user) => $user->file_number)
        ->addColumn('name', fn($user) => $user->name)
        ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
        ->addIndexColumn()
        ->make(true);
}
public function notReceivedEmployeesPage(Request $request, $subDepartmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');
    $subDepartment = departements::find($subDepartmentId);

    return view('reservation_subdeparts.not_reserved', [
        'subDepartmentId' => $subDepartmentId,
        'subDepartmentName' => $subDepartment->name ?? 'Unknown Subdepartment',
        'month' => $month,
        'year' => $year,
    ]);
}

public function getNotReceivedEmployees(Request $request, $subDepartmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $users = User::where('department_id', $subDepartmentId)
        ->whereNotIn('id', function ($query) use ($subDepartmentId, $month, $year) {
            $query->select('user_id')
                ->from('reservation_allowances')
                ->where('departement_id', $subDepartmentId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month);
        })
        ->with(['grade'])
        ->get();

    return DataTables::of($users)
        ->addColumn('file_number', fn($user) => $user->file_number)
        ->addColumn('name', fn($user) => $user->name)
        ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
        ->addIndexColumn()
        ->make(true);
}

    
}
