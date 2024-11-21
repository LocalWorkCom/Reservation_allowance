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
use App\Models\Sector;
use Illuminate\Support\Facades\Log;

class ReservationStaticsController extends Controller
{
    public function static($sector_id)
    {
        if (auth()->check() && auth()->user()->rule_id == 2) {
            $sector = Sector::find($sector_id);
    
            return view('reservation_statics.index', [
                'sector_id' => $sector_id,
                'sector_name' => $sector ? $sector->name : 'Unknown Sector'
            ]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }
    

    public function getAll(Request $request, $sector_id)
    {
        try {
            $month = $request->input('month');
            $year = $request->input('year');
    
            if (!$month || !$year) {
                return response()->json(['error' => 'Please select both month and year.'], 400);
            }
    
            $query = departements::withCount('children')
                ->where('sector_id', $sector_id)
                ->whereNull('parent_id'); // Only main departments
    
            $data = $query->orderBy('updated_at', 'desc')->get();
    
            return DataTables::of($data)
            ->addIndexColumn()
                ->addColumn('department_name', fn($row) => $row->name)
                ->addColumn('sub_departments_count', fn($row) => $row->children_count)
                ->addColumn('reservation_allowance_budget', function ($row) use ($month, $year) {
                   
                    $amount = DB::table('history_allawonces')
                        ->where('department_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->value('amount');
                        if (is_null($amount) || $amount == 0) {
                            return "ميزانية غير محدده"; // Open budget
                        }
                    return number_format($amount, 2) . ' د.ك';
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
    
                        if ( $historicalAmount == 0 || is_null( $historicalAmount)) {
                            return "-"; 
                       }
                       $remainingAmount = $historicalAmount - $registeredAmount;
                       return number_format($remainingAmount, 2) . " د.ك";
                })
                ->addColumn('number_of_employees', function ($row) {
                    return User::where('department_id', $row->id)->where('flag', 'employee')->count();
                })
                ->addColumn('received_allowance_count', function ($row) use ($month, $year) {
                    return ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                })
                ->addColumn('did_not_receive_allowance_count', function ($row) use ($month, $year) {
                    $employeesCount = User::where('department_id', $row->id)->where('flag', 'employee')->count();
                    $receivedAllowanceCount = ReservationAllowance::where('departement_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
    
                    return $employeesCount - $receivedAllowanceCount;
                })
                ->make(true);
        } catch (\Exception $e) {
            \Log::error("Error fetching departments: " . $e->getMessage());
    
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load data'
            ]);
        }
    }

    public function departmentEmployeesPage(Request $request, $departmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');
    $department = departements::find($departmentId);

    return view('reservation_statics.department_employees', [
        'departmentId' => $departmentId,
        'departmentName' => $department->name ?? 'Unknown Department',
        'month' => $month,
        'year' => $year,
    ]);
}

public function getDepartmentEmployees(Request $request, $departmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $users = User::where('department_id', $departmentId)
        ->with(['grade'])
        ->get();

    return DataTables::of($users)
        ->addColumn('file_number', fn($user) => $user->file_number)
        ->addColumn('name', fn($user) => $user->name)
        ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
        ->addIndexColumn()
        ->make(true);
}
public function notReceivedEmployeesPage(Request $request, $departmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');
    $department = departements::find($departmentId);

    return view('reservation_statics.not_reserved_employees', [
        'departmentId' => $departmentId,
        'departmentName' => $department->name ?? 'Unknown Department',
        'month' => $month,
        'year' => $year,
    ]);
}

public function getNotReceivedEmployees(Request $request, $departmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $users = User::where('department_id', $departmentId)
        ->whereNotIn('id', function ($query) use ($departmentId, $month, $year) {
            $query->select('user_id')
                ->from('reservation_allowances')
                ->where('departement_id', $departmentId)
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