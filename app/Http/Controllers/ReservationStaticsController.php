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

class ReservationStaticsController extends Controller
{
    public function static()
    {
        return view("reservation_statics.index");
    }
    public function getAll()
    {
        try {
            $userId = Auth::id();
    
            // Fetch only main departments (where parent_id is null)
            $data = departements::withCount('children')
                ->where('created_by', $userId)
                ->whereNull('parent_id')
                ->orderBy('updated_at', 'desc')
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
