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

class ReserveSectorController extends Controller
{
    public function static()
    {
        if (auth()->check() && in_array(auth()->user()->rule_id, [2, 4])) { 
            return view('reservation_sector.index');
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }
    

    public function getAll(Request $request)
    {
        try {
            $month = $request->input('month');
            $year = $request->input('year');
    
            if (!$month || !$year) {
                return response()->json([
                    'error' => 'Please select both month and year.'
                ], 400);
            }
    
            if (auth()->user()->rule_id == 2) { 
                $sectors = Sector::orderBy('name', 'asc')->get();
            } elseif (auth()->user()->rule_id == 4) { 
                $sectors = Sector::where('id', auth()->user()->sector)->get(); 
            } else {
                return response()->json([
                    'error' => 'Unauthorized action.'
                ], 403);
            }
    
            return DataTables::of($sectors)
                ->addIndexColumn()
                ->addColumn('sector', fn($row) => $row->name)
                ->addColumn('main_departments_count', function ($row) {
                    return departements::where('sector_id', $row->id)
                        ->whereNull('parent_id')
                        ->count();
                })
                ->addColumn('sub_departments_count', function ($row) {
                    return departements::where('sector_id', $row->id)
                        ->whereNotNull('parent_id')
                        ->count();
                })
                ->addColumn('reservation_allowance_budget', function ($row) use ($month, $year) {
                    $amount = DB::table('history_allawonces')
                    ->where('sector_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->orderBy('date', 'desc') 
                    ->value('amount'); 
                
                if (is_null($amount) || $amount == 0) {
                    return "ميزانية غير محدده"; 
                }
                
                return number_format($amount, 2) . ' د.ك';
                })
                // Total registered amount for the selected period
                ->addColumn('registered_amount', function ($row) use ($month, $year) {
                    $sum = ReservationAllowance::where('sector_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->sum('amount');
    
                    return number_format($sum, 2) . " د.ك";
                })
                // Remaining balance: historical budget minus registered amount
                ->addColumn('remaining_amount', function ($row) use ($month, $year) {
                   
                    $registeredAmount = ReservationAllowance::where('sector_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->sum('amount');
                        $historicalAmount = DB::table('history_allawonces')
                        ->where('sector_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->orderBy('date', 'desc') 
                        ->value('amount');
                    
                    if (is_null($historicalAmount) || $historicalAmount == 0) {
                        return "-"; 
                    }
                    
                    $remainingAmount = $historicalAmount - $registeredAmount;
                    
                    return number_format($remainingAmount, 2) . " د.ك";
                    
                })
                ->addColumn('employees_count', function ($row) use ($month, $year) {
                    return DB::table('user_departments')
                        ->where('sector_id', $row->id)
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                })
                ->addColumn('received_allowance_count', function ($row) use ($month, $year) {
                    return ReservationAllowance::where('sector_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                })
                ->addColumn('did_not_receive_allowance_count', function ($row) use ($month, $year) {
                    $userIdsInSector = DB::table('user_departments')
                        ->where('sector_id', $row->id)
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->distinct('user_id')
                        ->pluck('user_id');
                    $receivedAllowanceCount = ReservationAllowance::where('sector_id', $row->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->distinct('user_id')
                        ->count('user_id');
                    return $userIdsInSector->count() - $receivedAllowanceCount;
                })
                ->make(true);
        } catch (\Exception $e) {
            Log::error("Error fetching sectors: " . $e->getMessage());
    
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

