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
    
       
        if ($user && ($user->rule_id == 3 || $user->rule_id == 2)) {
            // Find the department where the current user is the manager
            $subDepartment = departements::where('manger', $user->id)->first();
    
            if ($subDepartment) {
                $sector = Sector::find($subDepartment->sector_id);
    
                $mainDepartment = departements::find($subDepartment->parent_id);
    
                $today = Carbon::now()->translatedFormat('l');
                $currentMonth = Carbon::now()->translatedFormat('F Y'); 
    
                $reservationAllowanceBudget = $subDepartment->reservation_allowance_amount;
    
                $recordedAmounts = ReservationAllowance::where('departement_id', $subDepartment->id)
                    ->sum('amount');
    
                $remainingAmount = $reservationAllowanceBudget - $recordedAmounts;
    
                return view('reservation_statics_credit.index', [
                    'sector' => $sector ? $sector->name : 'N/A', 
                    'mainDepartment' => $mainDepartment ? $mainDepartment->name : 'N/A', 
                    'subDepartment' => $subDepartment->name, 
                    'today' => $today, 
                    'currentMonth' => $currentMonth, 
                    'reservationAllowanceBudget' => $reservationAllowanceBudget, 
                    'recordedAmounts' => $recordedAmounts, 
                    'remainingAmount' => $remainingAmount 
                ]);
            } else {
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
            $subDepartment = departements::where('manger', $user->id)->first();
    
            if ($subDepartment) {
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
                        return Carbon::parse($row->date)->translatedFormat('l'); 
                    })
                    ->addColumn('prisoners_count', function($row) {
                        return $row->prisoners_count; 
                    })
                    ->addColumn('partial_reservation_count', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 2) 
                            ->count();
                    })
                    ->addColumn('full_reservation_count', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 1) 
                            ->count();
                    })
                    ->addColumn('partial_reservation_amount', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 2) 
                            ->sum('amount');
                    })
                    ->addColumn('full_reservation_amount', function($row) use ($subDepartment) {
                        return ReservationAllowance::where('departement_id', $subDepartment->id)
                            ->where('date', $row->date)
                            ->where('type', 1) 
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
                    ->addIndexColumn() 
                    ->rawColumns(['print']) 
                    ->make(true);
            }
        }
    
        return response()->json(['error' => 'Unauthorized'], 403);
    }


    public function printReport(Request $request)
{
    $date = $request->get('date');
    $user = Auth::user();

    $months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

    $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    $dayOfWeek = $days[Carbon::parse($date)->dayOfWeek]; // Get day of the week from the date

    $subDepartment = departements::where('manger', $user->id)->first();
    $reservations = ReservationAllowance::where('departement_id', $subDepartment->id)
        ->whereDate('date', $date)
        ->get();

    $partial_reservation_count = $reservations->where('type', 2)->count();
    $partial_reservation_amount = $reservations->where('type', 2)->sum('amount');

    $full_reservation_count = $reservations->where('type', 1)->count();
    $full_reservation_amount = $reservations->where('type', 1)->sum('amount');

    $total_amount = $partial_reservation_amount + $full_reservation_amount;

    $currentMonth = $months[date('n') - 1]; 

    return view('reservation_statics_credit.print', compact(
        'date', 'partial_reservation_count', 'partial_reservation_amount',
        'full_reservation_count', 'full_reservation_amount', 'total_amount', 'currentMonth', 'dayOfWeek'
    ));
}

    
    
}
