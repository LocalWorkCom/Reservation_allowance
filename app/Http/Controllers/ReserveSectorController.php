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
            ->addIndexColumn() // Automatically add an auto-incrementing column
            ->addColumn('sector', function ($row) {
                Log::info('Sector Name: ' . $row->name);
                return $row->name; // Sector name from the 'sectors' table
            })
            ->addColumn('main_departments_count', function ($row) {
                // Count of main departments for the given sector
                $count = departements::where('sector_id', $row->id)
                    ->whereNull('parent_id') // Only main departments
                    ->count();
                Log::info('Main departments count for Sector ID ' . $row->id . ': ' . $count);
                return $count;
            })
            ->addColumn('sub_departments_count', function ($row) {
                // Count of sub-departments for the given sector
                $count = departements::where('sector_id', $row->id)
                    ->whereNotNull('parent_id') // Only sub-departments
                    ->count();
                Log::info('Sub departments count for Sector ID ' . $row->id . ': ' . $count);
                return $count;
            })
            ->addColumn('reservation_allowance_budget', function ($row) {
                // Fetch 'reservation_allowance_amount' from 'sectors' table
                $amount = $row->reservation_allowance_amount;
                Log::info('Reservation Allowance Budget for Sector ID ' . $row->id . ': ' . $amount);
                return number_format($amount, 2);
            })
          
            ->addColumn('registered_amount', function ($row) {
                // Sum 'amount' from 'reservation_allowance' table for corresponding sector
                $sum = ReservationAllowance::where('sector_id', $row->id)->sum('amount');
                Log::info('Registered Amount for Sector ID ' . $row->id . ': ' . $sum);
                return number_format($sum, 2);
            })
            ->addColumn('remaining_amount', function ($row) {
                // Calculate remaining amount as "reservation_allowance_budget - registered_amount"
                $registeredAmount = ReservationAllowance::where('sector_id', $row->id)->sum('amount');
                $remainingAmount = $row->reservation_allowance_amount - $registeredAmount;
                Log::info('Remaining Amount for Sector ID ' . $row->id . ': ' . $remainingAmount);
                return number_format($remainingAmount, 2);
            })
            ->addColumn('employees_count', function ($row) {
                // Count users from 'users' table with the corresponding sector ID
                $count = User::where('sector', $row->id)->count();
                Log::info('Employees Count for Sector ID ' . $row->id . ': ' . $count);
                return $count;
            })
            ->addColumn('received_allowance_count', function ($row) {
                // Count the number of distinct 'user_id' who received allowance for the given sector
                $count = ReservationAllowance::where('sector_id', $row->id)
                    ->distinct('user_id')
                    ->count('user_id');
                Log::info('Received Allowance Count for Sector ID ' . $row->id . ': ' . $count);
                return $count;
            })
            ->addColumn('did_not_receive_allowance_count', function ($row) {
                // Calculate "did not receive allowance" as "employees_count - received_allowance_count"
                $employeesCount = User::where('sector', $row->id)->count();
                $receivedAllowanceCount = ReservationAllowance::where('sector_id', $row->id)
                    ->distinct('user_id')
                    ->count('user_id');
                $didNotReceiveAllowanceCount = $employeesCount - $receivedAllowanceCount;
                Log::info('Did Not Receive Allowance Count for Sector ID ' . $row->id . ': ' . $didNotReceiveAllowanceCount);
                return $didNotReceiveAllowanceCount;
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
