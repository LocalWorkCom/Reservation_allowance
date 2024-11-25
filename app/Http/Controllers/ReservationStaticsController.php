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
    public function static($sectorUuid)
{
    if (auth()->check() && auth()->user()->rule_id == 2) {
        $sector = Sector::where('uuid', $sectorUuid)->first();


        if (!$sector) {
            return abort(404, 'Sector not found');
        }
    
        return view('reservation_statics.index', [
            'sector_id' => $sector->uuid,
            'sector_name' => $sector->name,
        
        ]);
    } else {
        return abort(403, 'Unauthorized action.');
    }
}

    
public function getAll(Request $request, $sectorUuid)
{
    try {
        $month = $request->input('month');
        $year = $request->input('year');

        if (!$month || !$year) {
            return response()->json(['error' => 'Please select both month and year.'], 400);
        }

        // Fix: Correct the variable name to $sectorUuid
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }

        $query = departements::withCount('children')
            ->where('sector_id', $sector->id) // Use the sector's numeric ID
            ->whereNull('parent_id');

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
                return $amount ? number_format($amount, 2) . ' د.ك' : 'ميزانية غير محدده';
            })
            ->addColumn('registered_by', function ($row) use ($month, $year) {
                $sum = ReservationAllowance::where('departement_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');
                return number_format($sum, 2) . ' د.ك';
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
                return $historicalAmount ? number_format($historicalAmount - $registeredAmount, 2) . ' د.ك' : '-';
            })
            ->addColumn('number_of_employees', fn($row) => User::where('department_id', $row->id)->where('flag', 'employee')->count())
            ->addColumn('received_allowance_count', fn($row) => ReservationAllowance::where('departement_id', $row->id)->whereYear('date', $year)->whereMonth('date', $month)->distinct('user_id')->count('user_id'))
            ->addColumn('did_not_receive_allowance_count', function ($row) use ($month, $year) {
                $employeesCount = User::where('department_id', $row->id)->where('flag', 'employee')->count();
                $receivedAllowanceCount = ReservationAllowance::where('departement_id', $row->id)->whereYear('date', $year)->whereMonth('date', $month)->distinct('user_id')->count('user_id');
                return $employeesCount - $receivedAllowanceCount;
            })
            ->make(true);
    } catch (\Exception $e) {
        Log::error("Error fetching departments: " . $e->getMessage());
        return response()->json([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Failed to load data',
        ]);
    }
}



    public function departmentEmployeesPage(Request $request, $departmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $department = departements::where('uuid', $departmentUuid)->first();

        return view('reservation_statics.department_employees', [
            'departmentId' => $departmentUuid, // Pass UUID to the view
            'departmentName' => $department->name ?? 'Unknown Department',
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function getDepartmentEmployees(Request $request, $departmentUuid)
    {
        $department = departements::where('uuid', $departmentUuid)->first();
        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $users = User::where('department_id', $department->id)
            ->with(['grade'])
            ->get();

        return DataTables::of($users)
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
            ->addIndexColumn()
            ->make(true);
    }

    public function notReceivedEmployeesPage(Request $request, $departmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $department = departements::where('uuid', $departmentUuid)->first();

        return view('reservation_statics.not_reserved_employees', [
            'departmentId' => $departmentUuid, // Pass UUID to the view
            'departmentName' => $department->name ?? 'Unknown Department',
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function getNotReceivedEmployees(Request $request, $departmentUuid)
    {
        $department = departements::where('uuid', $departmentUuid)->first();
        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $month = $request->input('month');
        $year = $request->input('year');

        $users = User::where('department_id', $department->id)
            ->whereNotIn('id', function ($query) use ($department, $month, $year) {
                $query->select('user_id')
                    ->from('reservation_allowances')
                    ->where('departement_id', $department->id)
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
