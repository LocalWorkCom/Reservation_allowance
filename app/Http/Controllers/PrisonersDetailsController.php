<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReservationAllowance;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PrisonersDetailsController extends Controller
{
    public function getDetails($subDepartmentId, $date)
    {
        return view('prisoners_details.index', [
            'subDepartmentId' => $subDepartmentId,
            'date' => $date
        ]);
    }

    public function getData($subDepartmentId, $date)
    {
        $prisonersData = ReservationAllowance::where('departement_id', $subDepartmentId)
            ->whereDate('date', $date)
            ->with('user', 'grade')
            ->get();

        return DataTables::of($prisonersData)
            ->addColumn('name', fn($row) => $row->user->name ?? 'N/A')
            ->addColumn('amount', fn($row) => number_format($row->amount, 2) . " د.ك")
            ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
            ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
            ->addColumn('type', fn($row) => $row->type == 1 ? 'كلي' : 'جزئي')
            ->addColumn('grade', fn($row) => $row->grade->name ?? 'N/A')
            ->addIndexColumn()
            ->make(true);
    }
}
