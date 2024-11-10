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
    public function index(Request $request, $sectorId)
    {
        if (auth()->check() && auth()->user()->rule_id == 2) {
            $sector = Sector::find($sectorId);
            $month = $request->input('month');
            $year = $request->input('year');
    
            return view('sector_employees.index', [
                'sectorId' => $sectorId,
                'sectorName' => $sector ? $sector->name : 'Unknown Sector',
                'month' => $month,
                'year' => $year,
            ]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }
    
    public function getData(Request $request, $sectorId)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        $employees = User::whereIn('id', function ($query) use ($sectorId, $month, $year) {
            $query->select('user_id')
                  ->from('reservation_allowances')
                  ->where('sector_id', $sectorId)
                  ->whereYear('date', $year)
                  ->whereMonth('date', $month);
        })->with('grade')
          ->orderBy(function ($query) {
              $query->select('name')
                    ->from('grades')
                    ->whereColumn('grades.id', 'users.grade_id');
          })
          ->get();
    
        return DataTables::of($employees)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
            ->addColumn('days', function ($user) use ($sectorId, $month, $year) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                            ->where('sector_id', $sectorId)
                            ->where('type', 1)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->count();
    
                $partialDays = ReservationAllowance::where('user_id', $user->id)
                               ->where('sector_id', $sectorId)
                               ->where('type', 2)
                               ->whereYear('date', $year)
                               ->whereMonth('date', $month)
                               ->count();
    
                return "كلي: $fullDays | جزئي: $partialDays | مجموع: " . ($fullDays + $partialDays);
            })
            ->addColumn('allowance', function ($user) use ($sectorId, $month, $year) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                                 ->where('sector_id', $sectorId)
                                 ->where('type', 1)
                                 ->whereYear('date', $year)
                                 ->whereMonth('date', $month)
                                 ->sum('amount');
    
                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                    ->where('sector_id', $sectorId)
                                    ->where('type', 2)
                                    ->whereYear('date', $year)
                                    ->whereMonth('date', $month)
                                    ->sum('amount');
    
                $totalAllowance = $fullAllowance + $partialAllowance;
                return "كلي: " . number_format($fullAllowance, 2) . " د.ك | جزئي: " . number_format($partialAllowance, 2) . " د.ك | مجموع: " . number_format($totalAllowance, 2) . " د.ك";
            })
            ->addIndexColumn()
            ->make(true);
    }
    
    


    public function printReport(Request $request, $sectorId)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        $sector = Sector::find($sectorId);
    
        if ($sector) {
            // Fetch users ordered by grade
            $users = User::whereIn('id', function ($query) use ($sectorId, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('sector_id', $sectorId)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })->with(['grade', 'reservationAllowances'])
              ->orderBy(function ($query) {
                  $query->select('name')
                        ->from('grades')
                        ->whereColumn('grades.id', 'users.grade_id');
              })
              ->get();
    
            // Prepare reservation details for each user
            $userReservations = $users->map(function ($user) use ($sectorId, $month, $year) {
                $reservations = $user->reservationAllowances()
                    ->where('sector_id', $sectorId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->get();
    
                $fullDays = $reservations->where('type', 1)->count();
                $partialDays = $reservations->where('type', 2)->count();
                $totalDays = $fullDays + $partialDays;
    
                $fullAllowance = $reservations->where('type', 1)->sum('amount');
                $partialAllowance = $reservations->where('type', 2)->sum('amount');
                $totalAllowance = $fullAllowance + $partialAllowance;
    
                return [
                    'user' => $user,
                    'fullDays' => $fullDays,
                    'partialDays' => $partialDays,
                    'totalDays' => $totalDays,
                    'fullAllowance' => $fullAllowance,
                    'partialAllowance' => $partialAllowance,
                    'totalAllowance' => $totalAllowance,
                ];
            });
    
            // Create a new TCPDF instance
            $pdf = new TCPDF();
            $pdf->SetCreator('Your App');
            $pdf->SetTitle("Sector Reservation Report for {$sector->name}");
            $pdf->AddPage();
            $pdf->setRTL(true);
            $pdf->SetFont('dejavusans', '', 12);
    
            // Generate the PDF view content
            $html = view('sector_employees.sector_employees_report', [
                'sector' => $sector,
                'userReservations' => $userReservations,
                'month' => $month,
                'year' => $year,
            ])->render();
    
            $pdf->writeHTML($html, true, false, true, false, '');
            return $pdf->Output("sector_reservation_report_{$sector->name}.pdf", 'I');
        } else {
            return redirect()->back()->with('error', 'No sector found with this ID');
        }
    }
    
    
}

