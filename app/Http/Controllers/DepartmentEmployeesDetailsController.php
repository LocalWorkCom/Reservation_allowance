<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sector;
use App\Models\departements;
use App\Models\grade;
use App\Models\UserGrade;
use App\Models\ReservationAllowance;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use TCPDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DepartmentEmployeesDetailsController extends Controller
{
    public function index(Request $request, $departmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $department = departements::where('uuid', $departmentUuid)->first();

        return view('reserv_department_employees.index', [
            'departmentId' => $departmentUuid, // Pass UUID to the view
            'departmentName' => $department ? $department->name : 'Unknown Department',
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function getData($departmentUuid, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        $department = departements::where('uuid', $departmentUuid)->first();
        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }
    
        $employees = User::whereIn('id', function ($query) use ($department, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('departement_id', $department->id)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
            ->with(['department'])
            ->get();
    
        return DataTables::of($employees)
            ->addColumn('file_number', fn($user) => $user->file_number)
             ->addColumn('name', function ($user) use ( $department, $month, $year) {
                $latestRecord = DB::table('user_departments')
                    ->where('user_id', $user->id)
                    ->where('department_id',  $department->id)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('created_at', 'desc')
                    ->first();
            
                $transferred = $latestRecord && $latestRecord->flag == '0' ? ' (تم النقل)' : ''; 
                return $user->name . $transferred;
            })
            ->addColumn('grade', function ($user) use ($month, $year) {
                $latestUserGrade = UserGrade::where('user_id', $user->id)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('created_at', 'desc')
                    ->first();
    
                return $latestUserGrade && $latestUserGrade->grade
                    ? $latestUserGrade->grade->name
                    : 'N/A';
            })
            ->addColumn('full_days', function ($user) use ($department, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->count();
            })
            ->addColumn('partial_days', function ($user) use ($department, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->count();
            })
            ->addColumn('total_days', function ($user) use ($department, $month, $year) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->count();
    
                $partialDays = ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->count();
    
                return $fullDays + $partialDays;
            })
            ->addColumn('full_allowance', function ($user) use ($department, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->sum('amount');
            })
            ->addColumn('partial_allowance', function ($user) use ($department, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->sum('amount');
            })
            ->addColumn('total_allowance', function ($user) use ($department, $month, $year) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->sum('amount');
    
                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                    ->where('departement_id', $department->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->sum('amount');
    
                return $fullAllowance + $partialAllowance;
            })
            ->addIndexColumn()
            ->make(true);
    }
    


    public function allowanceDetailsPage(Request $request, $employeeUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $employee = User::where('uuid', $employeeUuid)->first();
    
        if (!$employee) {
            return abort(404, 'Employee not found');
        }
    
        return view('reservation_statics.allowance_details', [
            'employeeName' => $employee->name,
            'employeeUuid' => $employeeUuid, 
            'month' => $month,
            'year' => $year,
        ]);
    }
    

    public function getAllowanceDetails(Request $request, $employeeUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        $employee = User::where('uuid', $employeeUuid)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
    
        $allowances = ReservationAllowance::where('user_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
    
        return DataTables::of($allowances)
            ->addColumn('date', fn($allowance) => Carbon::parse($allowance->date)->format('Y-m-d'))
            ->addColumn('type', fn($allowance) => $allowance->type == 1 ? 'كلي' : 'جزئي')
            ->addColumn('amount', fn($allowance) => number_format($allowance->amount, 2) . ' د.ك')
            ->addColumn('created_by', function ($allowance) {
                $creator = User::find($allowance->created_by);
                return $creator ? $creator->name : 'غير معروف';
            })
            ->addColumn('created_at', function ($allowance) {
                return Carbon::parse($allowance->created_at)->format('Y-m-d H:i:s');
                Log::info('Allowance Details:', [
                    'created_by' => $allowance->created_by,
                    'created_at' => $allowance->created_at,
                ]);
            })
            ->addIndexColumn()
            ->make(true);
            Log::info('Allowance Details:', [
                'created_by' => $allowance->created_by,
                'created_at' => $allowance->created_at,
            ]);
    }
    

}
