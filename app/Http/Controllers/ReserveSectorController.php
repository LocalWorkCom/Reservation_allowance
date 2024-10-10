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
use App\Models\grade;
use App\Models\Sector;
use TCPDF;
use Illuminate\Support\Facades\Log;

class ReserveSectorController extends Controller
{
    public function static()
    {
        // Verify that the user is authorized
        $user = Auth::user();

        if ($user && $user->rule_id == 2) {
            return view('reservation_sector.index');
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }

    public function getAll()
    {
        try {
            // Log before attempting to fetch sectors
            Log::info('Attempting to fetch sectors data.');
    
            // Fetch all sectors
            $sectors = Sector::orderBy('name', 'asc')->get();
    
            // Log the number of sectors fetched
            Log::info('Number of sectors found: ' . $sectors->count());
    
            if ($sectors->isEmpty()) {
                Log::warning('No sectors found in the database.');
            }
    
            return DataTables::of($sectors)
                ->addIndexColumn()
                ->addColumn('sector', fn($row) => $row->name) // Sector name
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
                ->addColumn('reservation_allowance_budget', function ($row) {
                    $amount = number_format($row->reservation_allowance_amount, 2);
                    return "$amount د.ك";
                })
                ->addColumn('registered_amount', function ($row) {
                    $sum = ReservationAllowance::where('sector_id', $row->id)->sum('amount');
                    return number_format($sum, 2) . " د.ك";
                })
                ->addColumn('remaining_amount', function ($row) {
                    $registeredAmount = ReservationAllowance::where('sector_id', $row->id)->sum('amount');
                    $remainingAmount = $row->reservation_allowance_amount - $registeredAmount;
                    return number_format($remainingAmount, 2) . " د.ك";
                })
                ->addColumn('employees_count', function ($row) {
                    return User::where('sector', $row->id)->count();
                })
                ->addColumn('received_allowance_count', function ($row) {
                    return ReservationAllowance::where('sector_id', $row->id)
                        ->distinct('user_id')
                        ->count('user_id');
                })
                ->addColumn('did_not_receive_allowance_count', function ($row) {
                    $employeesCount = User::where('sector', $row->id)->count();
                    $receivedAllowanceCount = ReservationAllowance::where('sector_id', $row->id)
                        ->distinct('user_id')
                        ->count('user_id');
                    return $employeesCount - $receivedAllowanceCount;
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


