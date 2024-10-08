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
      
            $sector = Sector::find($sector_id);

            return view('reservation_statics.index', [
                'sector_id' => $sector_id,
                'sector_name' => $sector ? $sector->name : 'Unknown Sector'
            ]);
       
    }

    public function getAll($sector_id)
    {
        try {
           
    
            // Fetch only main departments that belong to the given sector
            $query = departements::withCount('children')
                ->where('sector_id', $sector_id)
                ->whereNull('parent_id');
    
            $data = $query->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
    
            return DataTables::of($data)
                ->addColumn('department_name', function($row) {
                    return $row->name;
                })
                ->addColumn('sub_departments_count', function($row) {

                    
                    return $row->children_count;
                })
                ->addColumn('reservation_allowance_budget', function($row) {
                    // Sum of 'reservation_allowance_amount' for sub-departments
                    $subDepartmentsSum = departements::where('parent_id', $row->id)
                        ->sum('reservation_allowance_amount');
    
                    // If no sub-departments, take the main department's own 'reservation_allowance_amount'
                    return $subDepartmentsSum > 0 ? $subDepartmentsSum : $row->reservation_allowance_amount;
                })
                ->addColumn('registered_by', function($row) {
                    // Fetch sub-department IDs for the main department
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
    
                    // Sum of 'amount' from the reservation_allowances table for those sub-departments
                    $totalRegisteredAmount = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)->sum('amount');
    
                    // If no sub-departments, take the main department's own 'amount'
                    if ($subDepartmentIds->isEmpty()) {
                        $totalRegisteredAmount = ReservationAllowance::where('departement_id', $row->id)->sum('amount');
                    }
    
                    return $totalRegisteredAmount;
                })
                ->addColumn('remaining_amount', function($row) {
                    // Calculate "المبلغ المتبقى" as "ميزانية بدل الحجز" - "المسجل"
    
                    // First, get the reservation allowance budget
                    $subDepartmentsSum = departements::where('parent_id', $row->id)
                        ->sum('reservation_allowance_amount');
                    $reservationAllowanceBudget = $subDepartmentsSum > 0 ? $subDepartmentsSum : $row->reservation_allowance_amount;
    
                    // Fetch sub-department IDs for the main department
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
    
                    // Then, calculate the total registered amount
                    $totalRegisteredAmount = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)->sum('amount');
                    if ($subDepartmentIds->isEmpty()) {
                        $totalRegisteredAmount = ReservationAllowance::where('departement_id', $row->id)->sum('amount');
                    }
    
                    // Calculate the remaining amount
                    return $reservationAllowanceBudget - $totalRegisteredAmount;
                })
                ->addColumn('number_of_employees', function($row) {
                    // Fetch sub-department IDs for the main department
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
    
                    // Count the users in these departments where 'flag' = 'employee'
                    $totalEmployees = User::whereIn('department_id', $subDepartmentIds)
                        ->where('flag', 'employee')
                        ->count();
    
                    // If no sub-departments, count the employees directly in the main department
                    if ($subDepartmentIds->isEmpty()) {
                        $totalEmployees = User::where('department_id', $row->id)
                            ->where('flag', 'employee')
                            ->count();
                    }
    
                    return $totalEmployees;
                })
                ->addColumn('received_allowance_count', function($row) {
                    // Fetch sub-department IDs for the main department
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
    
                    // Fetch the unique user_ids from the reservation_allowances table for these sub-departments
                    $uniqueUsers = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)
                        ->distinct('user_id')
                        ->count('user_id');
    
                    // If no sub-departments, check the main department's own user_ids
                    if ($subDepartmentIds->isEmpty()) {
                        $uniqueUsers = ReservationAllowance::where('departement_id', $row->id)
                            ->distinct('user_id')
                            ->count('user_id');
                    }
    
                    return $uniqueUsers;
                })
                ->addColumn('did_not_receive_allowance_count', function($row) {
                    // Calculate "لم يحصل على بدل حجز" as "عدد الموظفين" - "الحاصلين على بدل حجز"
    
                    // Fetch sub-department IDs for the main department
                    $subDepartmentIds = departements::where('parent_id', $row->id)->pluck('id');
    
                    // Count the users in these departments where 'flag' = 'employee'
                    $totalEmployees = User::whereIn('department_id', $subDepartmentIds)
                        ->where('flag', 'employee')
                        ->count();
    
                    // If no sub-departments, count the employees directly in the main department
                    if ($subDepartmentIds->isEmpty()) {
                        $totalEmployees = User::where('department_id', $row->id)
                            ->where('flag', 'employee')
                            ->count();
                    }
    
                    // Fetch the unique user_ids from the reservation_allowances table for these sub-departments
                    $uniqueUsers = ReservationAllowance::whereIn('departement_id', $subDepartmentIds)
                        ->distinct('user_id')
                        ->count('user_id');
    
                    // If no sub-departments, check the main department's own user_ids
                    if ($subDepartmentIds->isEmpty()) {
                        $uniqueUsers = ReservationAllowance::where('departement_id', $row->id)
                            ->distinct('user_id')
                            ->count('user_id');
                    }
    
                    // "لم يحصل على بدل حجز" = "عدد الموظفين" - "الحاصلين على بدل حجز"
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