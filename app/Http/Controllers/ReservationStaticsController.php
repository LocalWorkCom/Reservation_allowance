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
    
            $query = departements::withCount('children')
                ->where('sector_id', $sector_id)
                ->whereNull('parent_id');
    
            $data = $query->orderBy('updated_at', 'desc')->get();
    
            return DataTables::of($data)
                ->addColumn('department_name', fn($row) => $row->name)
                ->addColumn('sub_departments_count', fn($row) => $row->children_count)
                ->addColumn('reservation_allowance_budget', function($row) {
                    $subDepartmentsSum = departements::where('parent_id', $row->id)->sum('reservation_allowance_amount');
                    $budget = $subDepartmentsSum > 0 ? $subDepartmentsSum : $row->reservation_allowance_amount;
                    return number_format($budget, 2) . " د.ك";
                })
                ->addColumn('registered_by', function($row) use ($month, $year) {
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
                    $totalRegisteredAmount = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->sum('amount');
                    if ($subDepartmentIds->isEmpty()) {
                        $totalRegisteredAmount = ReservationAllowance::where('departement_id', $row->id)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->sum('amount');
                    }
                    return number_format($totalRegisteredAmount, 2) . " د.ك";
                })
                ->addColumn('remaining_amount', function($row) use ($month, $year) {
                    $subDepartmentsSum = departements::where('parent_id', $row->id)->sum('reservation_allowance_amount');
                    $reservationAllowanceBudget = $subDepartmentsSum > 0 ? $subDepartmentsSum : $row->reservation_allowance_amount;
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
                    $totalRegisteredAmount = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->sum('amount');
                    if ($subDepartmentIds->isEmpty()) {
                        $totalRegisteredAmount = ReservationAllowance::where('departement_id', $row->id)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->sum('amount');
                    }
                    $remainingAmount = $reservationAllowanceBudget - $totalRegisteredAmount;
                    return number_format($remainingAmount, 2) . " د.ك";
                })
                ->addColumn('number_of_employees', function($row) {
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
                    $totalEmployees = User::whereIn('department_id', $subDepartmentIds)->where('flag', 'employee')->count();
                    if ($subDepartmentIds->isEmpty()) {
                        $totalEmployees = User::where('department_id', $row->id)->where('flag', 'employee')->count();
                    }
                    return $totalEmployees;
                })
                ->addColumn('received_allowance_count', function($row) use ($month, $year) {
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
                    $uniqueUsers = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                    if ($subDepartmentIds->isEmpty()) {
                        $uniqueUsers = ReservationAllowance::where('departement_id', $row->id)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->distinct('user_id')
                            ->count('user_id');
                    }
                    return $uniqueUsers;
                })
                ->addColumn('did_not_receive_allowance_count', function($row) use ($month, $year) {
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
                    $totalEmployees = User::whereIn('department_id', $subDepartmentIds)->where('flag', 'employee')->count();
                    if ($subDepartmentIds->isEmpty()) {
                        $totalEmployees = User::where('department_id', $row->id)->where('flag', 'employee')->count();
                    }
                    $uniqueUsers = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                    if ($subDepartmentIds->isEmpty()) {
                        $uniqueUsers = ReservationAllowance::where('departement_id', $row->id)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->distinct('user_id')
                            ->count('user_id');
                    }
                    return $totalEmployees - $uniqueUsers;
                })
                ->rawColumns(['action'])
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
    
    
}