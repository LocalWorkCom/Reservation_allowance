<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ReservationAllowance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\grade;
use App\Models\Sector;
use TCPDF;
use Illuminate\Support\Facades\Log;

class SectorEmployeesDetailsController extends Controller
{
    public function index($sectorId)
    {
        return view('sector_employees.index', ['sectorId' => $sectorId]);
    }

    public function getData($sectorId)
    {
        // Get unique users from ReservationAllowance based on sector ID
        $employees = User::whereIn('id', function ($query) use ($sectorId) {
            $query->select('user_id')
                  ->from('reservation_allowances')
                  ->where('sector_id', $sectorId);
        })->with('grade')->get();

        return DataTables::of($employees)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
            ->addColumn('days', function ($user) use ($sectorId) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                            ->where('sector_id', $sectorId)
                            ->where('type', 1)
                            ->count();

                $partialDays = ReservationAllowance::where('user_id', $user->id)
                               ->where('sector_id', $sectorId)
                               ->where('type', 2)
                               ->count();

                $totalDays = $fullDays + $partialDays;
                return "كلي: $fullDays | جزئي: $partialDays | مجموع: $totalDays";
            })
            ->addColumn('allowance', function ($user) use ($sectorId) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                                 ->where('sector_id', $sectorId)
                                 ->where('type', 1)
                                 ->sum('amount');

                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                    ->where('sector_id', $sectorId)
                                    ->where('type', 2)
                                    ->sum('amount');

                $totalAllowance = $fullAllowance + $partialAllowance;
                return "كلي: " . number_format($fullAllowance, 2) . " | جزئي: " . number_format($partialAllowance, 2) . " | مجموع: " . number_format($totalAllowance, 2);
            })
            ->addIndexColumn()
            ->make(true);
    }
}

