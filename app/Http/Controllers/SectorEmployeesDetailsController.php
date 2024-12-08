<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ReservationAllowance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\grade;
use App\Models\Sector;
use App\Models\UserGrade;
use TCPDF;
use Illuminate\Support\Facades\Log;

class SectorEmployeesDetailsController extends Controller
{
    public function index(Request $request, $sectorUuid)
    {
        if (auth()->check() && auth()->user()->rule_id == 2) {
            $sector = Sector::where('uuid', $sectorUuid)->first();
            $month = $request->input('month');
            $year = $request->input('year');

            return view('sector_employees.index', [
                'sectorId' => $sectorUuid,
                'sectorName' => $sector ? $sector->name : 'Unknown Sector',
                'month' => $month,
                'year' => $year,
            ]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }
    
    public function getData($sectorUuid, Request $request)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }
    
        $month = $request->input('month');
        $year = $request->input('year');
    
        $employees = User::whereIn('id', function ($query) use ($sector, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('sector_id', $sector->id)
                      ->whereNull('departement_id') 
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
      
            ->get();
    
        return DataTables::of($employees)
            ->addColumn('file_number', fn($user) => $user->file_number)
            // ->addColumn('name', fn($user) => $user->name)
            ->addColumn('name', function ($user) use ($sector, $month, $year) {
                $latestRecord = DB::table('user_departments')
                    ->where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('department_id') 
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('created_at', 'desc')
                    ->first();
            
                $transferred = $latestRecord && $latestRecord->flag == '0' ? ' (تم النقل)' : ''; 
                return $user->name . $transferred;
            })
            
            ->addColumn('grade', function ($user) use ($month, $year) {
                $lastGrade = UserGrade::where('user_id', $user->id)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('created_at', 'desc') 
                    ->with('grade') 
                    ->first();
    
                return $lastGrade && $lastGrade->grade ? $lastGrade->grade->name : 'N/A';
            })
            ->addColumn('full_days', function ($user) use ($sector, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->count();
            })
            ->addColumn('partial_days', function ($user) use ($sector, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->count();
            })
            ->addColumn('total_days', function ($user) use ($sector, $month, $year) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->count();
    
                $partialDays = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->count();
    
                return $fullDays + $partialDays;
            })
            ->addColumn('full_allowance', function ($user) use ($sector, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->sum('amount');
            })
            ->addColumn('partial_allowance', function ($user) use ($sector, $month, $year) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->sum('amount');
            })
            ->addColumn('total_allowance', function ($user) use ($sector, $month, $year) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->sum('amount');
    
                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 2)
                    ->sum('amount');
    
                return $fullAllowance + $partialAllowance;
            })
            ->addIndexColumn()
            ->make(true);
    }
    
    
    
    


    public function notReservedUsers(Request $request, $sectorUuid)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return abort(404, 'Sector not found');
        }
    
        $month = $request->input('month');
        $year = $request->input('year');
    
        return view('sector_employees.not_reserved', [
            'sectorId' => $sectorUuid, // Pass UUID
            'sectorName' => $sector->name,
            'month' => $month,
            'year' => $year,
        ]);
    }
    
    public function getNotReservedData(Request $request, $sectorUuid)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }
    
        $month = $request->input('month');
        $year = $request->input('year');
    
        $userIdsInSector = DB::table('user_departments')
            ->where('sector_id', $sector->id)
            ->whereNull('department_id') 
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->distinct('user_id')
            ->pluck('user_id');
    
        $users = User::whereIn('id', $userIdsInSector)
            ->whereNotIn('id', function ($query) use ($sector, $month, $year) {
                $query->select('user_id')
                    ->from('reservation_allowances')
                    ->where('sector_id', $sector->id)
                    ->whereNull('departement_id') 
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);
            })
            ->with(['department'])
            ->get();
    
        return DataTables::of($users)
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', function ($user) use ($sector, $month, $year) {
                $latestRecord = DB::table('user_departments')
                    ->where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('department_id') 
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
            ->addIndexColumn()
            ->make(true);
    }
    
    
    public function sectorUsersPage(Request $request, $sectorUuid)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return abort(404, 'Sector not found');
        }
    
        $month = $request->input('month');
        $year = $request->input('year');
    
        return view('sector_employees.sector_users', [
            'sectorId' => $sectorUuid,
            'sectorName' => $sector->name,
            'month' => $month,
            'year' => $year,
        ]);
    }
    
    
    
    public function getSectorUsers(Request $request, $sectorUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        if (!$month || !$year) {
            return response()->json([
                'data' => [], 
                'error' => 'Month and Year are required.',
            ]);
        }
    
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }
    
        $userIdsInSector = DB::table('user_departments')
            ->where('sector_id', $sector->id)
            ->whereNull('department_id') 
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->select('user_id', DB::raw('MAX(created_at) as latest_created_at')) 
            ->groupBy('user_id') 
            ->pluck('user_id');
    
            $latestGrades = DB::table('user_grades')
            ->select('user_id', DB::raw('MAX(created_at) as latest_created_at'), DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(grade_id ORDER BY created_at DESC), ",", 1) as grade_id'))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('user_id');
        
        $users = User::whereIn('users.id', $userIdsInSector)
            ->leftJoinSub($latestGrades, 'latest_user_grades', function ($join) {
                $join->on('users.id', '=', 'latest_user_grades.user_id');
            })
            ->leftJoin('grades', 'latest_user_grades.grade_id', '=', 'grades.id')
            ->select('users.*', 'grades.name as grade_name', 'grades.type as grade_type', 'grades.order as grade_order')
            ->orderBy('grade_type', 'asc')  
            ->orderBy('grade_order', 'asc') 
            ->get();
        
    
    
        // Return data to DataTables
        return DataTables::of($users)
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', function ($user) use ($sector, $month, $year) {
                $latestRecord = DB::table('user_departments')
                    ->where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereNull('department_id') 
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('created_at', 'desc')
                    ->first();
            
                $transferred = $latestRecord && $latestRecord->flag == '0' ? ' (تم النقل)' : ''; 
                return $user->name . $transferred;
            })
            ->addColumn('grade', fn($user) => $user->grade_name ?? 'N/A') // Display grade name directly
            ->addIndexColumn()
            ->make(true);
    }
    

    
    public function allowanceDetailsPage(Request $request, $employeeUuid)
{
    $month = $request->input('month');
    $year = $request->input('year');
    $sectorId = $request->input('sectorId'); 

    $employee = User::where('uuid', $employeeUuid)->first();
    if (!$employee) {
        return abort(404, 'Employee not found');
    }

    $sector = Sector::where('uuid',$sectorId)->first(); 

    return view('sector_employees.allowance_details', [
        'employeeName' => $employee->name,
        'employeeUuid' => $employeeUuid,
        'sectorName' => $sector->name ?? 'Unknown Sector',
        'month' => $month,
        'year' => $year,
        'sectorId' => $sectorId 
    ]);
}
public function getAllowanceDetails(Request $request, $employeeUuid)
{
    $month = $request->input('month');
    $year = $request->input('year');
    $sectorUuid = $request->input('sectorId'); 

    Log::info('Allowance Details Request', [
        'employeeUuid' => $employeeUuid,
        'month' => $month,
        'year' => $year,
        'sectorUuid' => $sectorUuid,
    ]);

    $employee = User::where('uuid', $employeeUuid)->first();
    if (!$employee) {
        Log::warning('Employee not found', ['employeeUuid' => $employeeUuid]);
        return response()->json(['error' => 'Employee not found'], 404);
    }

    Log::info('Employee Found', [
        'employeeId' => $employee->id,
        'employeeName' => $employee->name,
    ]);

    $sector = Sector::where('uuid', $sectorUuid)->first();
    if (!$sector) {
        Log::warning('Sector not found', ['sectorUuid' => $sectorUuid]);
        return response()->json(['error' => 'Sector not found'], 404);
    }

    $allowances = ReservationAllowance::where('user_id', $employee->id)
        ->where('sector_id', $sector->id)
        ->whereNull('departement_id')
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->get();

    Log::info('Fetched Allowances', [
        'employeeId' => $employee->id,
        'allowances' => $allowances->toArray(),
    ]);

    return DataTables::of($allowances)
        ->addColumn('date', function ($allowance) {
            return Carbon::parse($allowance->date)->format('Y-m-d');
        })
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
}



    
    
}