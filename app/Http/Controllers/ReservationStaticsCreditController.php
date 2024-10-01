<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\departements;
use App\Models\Sector;
use App\Models\ReservationAllowance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ReservationStaticsCreditController extends Controller
{
    public function static()
    {
        $user = Auth::user();
    
        // Check if the user is either a manager or super admin
        if ($user && ($user->rule_id == 3 || $user->rule_id == 2)) {
            // Find the department where the current user is the manager
            $subDepartment = departements::where('manger', $user->id)->first();
    
            if ($subDepartment) {
                // Fetch sector name from the sector_id
                $sector = Sector::find($subDepartment->sector_id);
    
                // Fetch parent department (Main department)
                $mainDepartment = departements::find($subDepartment->parent_id);
    
                // Get today's date and the current month in Arabic
                $today = Carbon::now()->translatedFormat('l');
                $currentMonth = Carbon::now()->translatedFormat('F Y'); // e.g., "October 2024"
    
                // Fetch the "ميزانية بدل الحجز" from the 'reservation_allowance_amount' column in departments
                $reservationAllowanceBudget = $subDepartment->reservation_allowance_amount;
    
                // Calculate the "مبالغ مسجله" by summing the 'amount' from reservation_allowances for this department
                $recordedAmounts = ReservationAllowance::where('departement_id', $subDepartment->id)
                    ->sum('amount');
    
                // Calculate the "المتبقى" by subtracting the recorded amounts from the reservation budget
                $remainingAmount = $reservationAllowanceBudget - $recordedAmounts;
    
                // Pass data to the view
                return view('reservation_statics_credit.index', [
                    'sector' => $sector ? $sector->name : 'N/A', // Sector name
                    'mainDepartment' => $mainDepartment ? $mainDepartment->name : 'N/A', // Main department name
                    'subDepartment' => $subDepartment->name, // Sub-department name
                    'today' => $today, // Today's date
                    'currentMonth' => $currentMonth, // Current month
                    'reservationAllowanceBudget' => $reservationAllowanceBudget, // Reservation allowance budget
                    'recordedAmounts' => $recordedAmounts, // Recorded amounts
                    'remainingAmount' => $remainingAmount // Remaining amount
                ]);
            } else {
                // Redirect or show an error if no department is found
                return abort(404, 'No department found for the manager.');
            }
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }
    
    public function getAll()
    {
        $user = Auth::user();
    
        if ($user && ($user->rule_id == 3 || $user->rule_id == 2)) {
            // Get the current department where the user is the manager
            $subDepartment = departements::where('manger', $user->id)->first();
    
            if ($subDepartment) {
                // Get all reservation allowances for the current month
                $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
                $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString();
    
                $reservationData = ReservationAllowance::whereBetween('date', [$currentMonthStart, $currentMonthEnd])
                    ->where('departement_id', $subDepartment->id)
                    ->selectRaw('date, COUNT(user_id) as prisoners_count')
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();
    
                return DataTables::of($reservationData)
                    ->addColumn('day', function($row) {
                        return Carbon::parse($row->date)->translatedFormat('l'); // Translated day in Arabic
                    })
                    ->addColumn('prisoners_count', function($row) {
                        return $row->prisoners_count; // Total number of reserved people for that day
                    })
                    ->addColumn('partial_reservation_count', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 2) // Partial reservation
                            ->count();
                    })
                    ->addColumn('full_reservation_count', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 1) // Full reservation
                            ->count();
                    })
                    ->addColumn('partial_reservation_amount', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 2) // Partial reservation
                            ->sum('amount');
                    })
                    ->addColumn('full_reservation_amount', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 1) // Full reservation
                            ->sum('amount');
                    })
                    ->addColumn('total_amount', function($row) use ($subDepartment) {
                        $partialAmount = ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 2)
                            ->sum('amount');
                        $fullAmount = ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 1)
                            ->sum('amount');
                        return $partialAmount + $fullAmount;
                    })
                    ->addColumn('print', function($row) {
                        return '<button class="btn btn-sm btn-primary" onclick="printReport(\'' . $row->date . '\')">طباعة</button>';
                    })
                    ->addIndexColumn() // This adds the automatic numbering (الترتيب)
                    ->rawColumns(['print']) // Make sure to mark print column as raw HTML
                    ->make(true);
            }
        }
    
        return response()->json(['error' => 'Unauthorized'], 403);
    }


    public function printReport(Request $request)
{
    $date = $request->get('date');
    $user = Auth::user();

    // Get the current month names in Arabic
    $months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

    // Get the day name in Arabic for the selected date
    $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    $dayOfWeek = $days[Carbon::parse($date)->dayOfWeek]; // Get day of the week from the date

    // Get data for the selected date
    $subDepartment = departements::where('manger', $user->id)->first();
    $reservations = ReservationAllowance::where('departement_id', $subDepartment->id)
        ->whereDate('date', $date)
        ->get();

    $partial_reservation_count = $reservations->where('type', 2)->count();
    $partial_reservation_amount = $reservations->where('type', 2)->sum('amount');

    $full_reservation_count = $reservations->where('type', 1)->count();
    $full_reservation_amount = $reservations->where('type', 1)->sum('amount');

    $total_amount = $partial_reservation_amount + $full_reservation_amount;

    // Get the current month in Arabic
    $currentMonth = $months[date('n') - 1]; 

    return view('reservation_statics_credit.print', compact(
        'date', 'partial_reservation_count', 'partial_reservation_amount',
        'full_reservation_count', 'full_reservation_amount', 'total_amount', 'currentMonth', 'dayOfWeek'
    ));
}

    
    
}
