<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use App\Models\ReservationAllowance;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class SubDepartmentStatsController extends Controller
{
    public function index(Request $request, $department_id)
{
    if (auth()->check() && auth()->user()->rule_id == 2) {
        $mainDepartment = departements::find($department_id);
        $month = $request->input('month');
        $year = $request->input('year');

        return view('reservation_subdeparts.index', [
            'department_id' => $department_id,
            'main_department_name' => $mainDepartment ? $mainDepartment->name : 'Unknown Department',
            'month' => $month,
            'year' => $year
        ]);
    } else {
        return abort(403, 'Unauthorized action.');
    }
}


public function getAll(Request $request, $subDepartmentId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $reservationData = ReservationAllowance::where('departement_id', $subDepartmentId)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
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
        ->rawColumns(['prisoners_count', 'print'])
        ->make(true);
}


}
