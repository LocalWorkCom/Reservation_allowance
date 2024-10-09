<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\departements;
use App\Models\ReservationAllowance;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class SubDepartmentReservationController extends Controller
{
    public function static($subDepartmentId)
    {
        $subDepartment = departements::find($subDepartmentId);
        
        if ($subDepartment) {
            $sector = $subDepartment->sector->name ?? 'N/A';
            $mainDepartment = $subDepartment->parent->name ?? 'N/A';
            $today = Carbon::now()->translatedFormat('l');
            $currentMonth = Carbon::now()->translatedFormat('F Y');

            $reservationAllowanceBudget = $subDepartment->reservation_allowance_amount;
            $recordedAmounts = ReservationAllowance::where('departement_id', $subDepartmentId)->sum('amount');
            $remainingAmount = $reservationAllowanceBudget - $recordedAmounts;

            return view('subdepartment_reservation.index', [
                'sector' => $sector,
                'mainDepartment' => $mainDepartment,
                'subDepartment' => $subDepartment->name,
                'today' => $today,
                'currentMonth' => $currentMonth,
                'reservationAllowanceBudget' => $reservationAllowanceBudget,
                'recordedAmounts' => $recordedAmounts,
                'remainingAmount' => $remainingAmount,
                'subDepartmentId' => $subDepartmentId // Pass this variable to the view
            ]);
        } else {
            return abort(404, 'Sub-department not found');
        }
    }
    
    public function getAll($subDepartmentId)
    {
        $reservationData = ReservationAllowance::where('departement_id', $subDepartmentId)
            ->selectRaw('date, COUNT(user_id) as prisoners_count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    
        return DataTables::of($reservationData)
            ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
            ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
            ->addColumn('prisoners_count', function ($row) use ($subDepartmentId) {
                $url = route('prisoners.details', ['subDepartmentId' => $subDepartmentId, 'date' => $row->date]);
                return '<a href="' . $url . '" style="color:blue !important">' . $row->prisoners_count . '</a>';
            })
            ->addColumn('partial_reservation_count', fn($row) => ReservationAllowance::where('departement_id', $subDepartmentId)->where('date', $row->date)->where('type', 2)->count())
            ->addColumn('partial_reservation_amount', fn($row) => ReservationAllowance::where('departement_id', $subDepartmentId)->where('date', $row->date)->where('type', 2)->sum('amount'))
            ->addColumn('full_reservation_count', fn($row) => ReservationAllowance::where('departement_id', $subDepartmentId)->where('date', $row->date)->where('type', 1)->count())
            ->addColumn('full_reservation_amount', fn($row) => ReservationAllowance::where('departement_id', $subDepartmentId)->where('date', $row->date)->where('type', 1)->sum('amount'))
            ->addColumn('total_amount', function ($row) use ($subDepartmentId) {
                $partialAmount = ReservationAllowance::where('departement_id', $subDepartmentId)->where('date', $row->date)->where('type', 2)->sum('amount');
                $fullAmount = ReservationAllowance::where('departement_id', $subDepartmentId)->where('date', $row->date)->where('type', 1)->sum('amount');
                return $partialAmount + $fullAmount;
            })
            ->addColumn('print', fn($row) => '<button class="btn btn-sm btn-primary" onclick="printReport(\'' . $row->date . '\')">طباعة</button>')
            ->addIndexColumn()
            ->rawColumns(['prisoners_count', 'print']) // Ensure raw HTML rendering for these columns
            ->make(true);
    }
    
}
