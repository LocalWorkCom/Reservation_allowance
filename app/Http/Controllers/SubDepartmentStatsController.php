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
    public function index($departmentUuid, Request $request)
    {
        $department = departements::where('uuid', $departmentUuid)->first();

        return view('reservation_subdeparts.index', [
            'departmentId' => $departmentUuid,
            'departmentName' => $department ? $department->name : 'Unknown Department',
            'month' => $request->query('month'),
            'year' => $request->query('year'),
        ]);
    }

    public function getAll($departmentUuid, Request $request)
    {
        try {
            $month = $request->query('month');
            $year = $request->query('year');

            if (!$month || !$year) {
                return response()->json(['error' => 'Please select both month and year.'], 400);
            }

            $department = departements::where('uuid', $departmentUuid)->first();
            if (!$department) {
                return response()->json(['error' => 'Department not found'], 404);
            }

            $query = departements::where('parent_id', $department->id);

            return DataTables::of($query)
                ->addColumn('sub_department_name', fn($row) => $row->name)
                ->addColumn('reservation_allowance_budget', function ($row) use ($month, $year) {
                    $amount = DB::table('history_allawonces')
                    ->where('department_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');

                // Check if the amount is null or zero
                if (is_null($amount) || $amount == 0) {
                    return "ميزانية غير محدده"; // Return "Unspecified Budget"
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

                    return $historicalAmount
                        ? number_format($historicalAmount - $registeredAmount, 2) . " د.ك"
                        : "-";
                })
                ->addColumn('employees_count', function ($row) use ($month, $year) {
                    return DB::table('user_departments')
                        ->where('department_id', $row->id) 
                        ->whereYear('created_at', $year) 
                        ->whereMonth('created_at', $month) 
                        ->distinct('user_id') 
                        ->count('user_id');
                })
                ->addColumn('received_allowance_count', function ($row) use ($month, $year) {
                    return ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year) 
                        ->whereMonth('date', $month) 
                        ->distinct('user_id') 
                        ->count('user_id');
                })
                ->addColumn('did_not_receive_allowance_count', function ($row) use ($month, $year) {
                    $employees = DB::table('user_departments')
                        ->where('department_id', $row->id)
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->distinct('user_id')
                        ->pluck('user_id'); 
                
                    $receivedAllowanceCount = ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                
                    return $employees->count() - $receivedAllowanceCount;
                })
                
                ->make(true);
        } catch (\Exception $e) {
            \Log::error("Error fetching sub-departments for department UUID $departmentUuid: " . $e->getMessage());

            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load sub-department data. Please try again later.',
            ]);
        }
    }

    public function subDepartmentEmployeesPage(Request $request, $subDepartmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $subDepartment = departements::where('uuid', $subDepartmentUuid)->first();
    
        return view('reservation_subdeparts.subdeparts_employee', [
            'subDepartmentId' => $subDepartmentUuid,
            'subDepartmentName' => $subDepartment->name ?? 'Unknown Subdepartment',
            'month' => $month,
            'year' => $year,
        ]);
    }
    
    public function getSubDepartmentEmployees(Request $request, $subDepartmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        $subDepartment = departements::where('uuid', $subDepartmentUuid)->first();
        if (!$subDepartment) {
            return response()->json(['error' => 'Sub-department not found'], 404);
        }
    
        $users = DB::table('user_departments')
            ->where('user_departments.department_id', $subDepartment->id) 
            ->whereYear('user_departments.created_at', $year) 
            ->whereMonth('user_departments.created_at', $month) 
            ->join('users', 'user_departments.user_id', '=', 'users.id') 
            ->distinct('users.id') 
            ->select('users.*') 
            ->get();
    
        return DataTables::of($users)
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', function ($user) {
                $grade = DB::table('grades')->where('id', $user->grade_id)->value('name');
                return $grade ?? 'N/A';
            })
            ->addIndexColumn()
            ->make(true);
    }
    
    
    public function notReceivedEmployeesPage(Request $request, $subDepartmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $subDepartment = departements::where('uuid', $subDepartmentUuid)->first();
    
        return view('reservation_subdeparts.not_reserved', [
            'subDepartmentId' => $subDepartmentUuid,
            'subDepartmentName' => $subDepartment->name ?? 'Unknown Subdepartment',
            'month' => $month,
            'year' => $year,
        ]);
    }
    
    public function getNotReceivedEmployees(Request $request, $subDepartmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        $subDepartment = departements::where('uuid', $subDepartmentUuid)->first();
        if (!$subDepartment) {
            return response()->json(['error' => 'Sub-department not found'], 404);
        }
    
        $employees = DB::table('user_departments')
            ->where('user_departments.department_id', $subDepartment->id) 
            ->whereYear('user_departments.created_at', $year) 
            ->whereMonth('user_departments.created_at', $month) 
            ->join('users', 'user_departments.user_id', '=', 'users.id')
            ->distinct('users.id') 
            ->select('users.*');
    
        $users = $employees->whereNotIn('users.id', function ($query) use ($subDepartment, $month, $year) {
            $query->select('user_id')
                ->from('reservation_allowances')
                ->where('reservation_allowances.departement_id', $subDepartment->id)
                ->whereYear('reservation_allowances.date', $year) 
                ->whereMonth('reservation_allowances.date', $month); 
        })->get();
    
        return DataTables::of($users)
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', function ($user) {
                $grade = DB::table('grades')->where('id', $user->grade_id)->value('name');
                return $grade ?? 'N/A';
            })
            ->addIndexColumn()
            ->make(true);
    }
    
    
}
