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
        // When clicking on "no of reserved employee" in sectors static
    public function index($sectorId)
    {
        if (auth()->check() && auth()->user()->rule_id == 2) {
            $sector = Sector::find($sectorId);

            return view('sector_employees.index', [
                'sectorId' => $sectorId,
                'sectorName' => $sector ? $sector->name : 'Unknown Sector'
            ]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
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
            return "كلي: " . number_format($fullAllowance, 2) . " د.ك | جزئي: " . number_format($partialAllowance, 2) . " د.ك | مجموع: " . number_format($totalAllowance, 2) . " د.ك";
        })
        ->addIndexColumn()
        ->make(true);
}


    public function printReport($sectorId)
    {
        // Fetch the sector details
        $sector = Sector::find($sectorId);
    
        if ($sector) {
            // Fetch all users in this sector and their reservation data
            $users = User::where('sector', $sectorId)->with(['grade', 'reservationAllowances'])->get();
    
            // Prepare reservation details for each user
            $userReservations = $users->map(function ($user) {
                $reservations = $user->reservationAllowances;
    
                // Calculate days and amounts for "حجز كلي" (type 1) and "حجز جزئي" (type 2)
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
    
            // Set document information
            $pdf->SetCreator('Your App');
            $pdf->SetAuthor('Your App');
            $pdf->SetTitle('Sector Reservation Report');
            $pdf->SetSubject('Report');
    
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
            // Set margins
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);
    
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 10);
    
            // Set font for Arabic
            $pdf->SetFont('dejavusans', '', 12);
    
            // Add a page
            $pdf->AddPage();
    
            // Set RTL direction
            $pdf->setRTL(true);
    
            // Write HTML content for the PDF view
            $html = view('sector_employees.sector_employees_report', [
                'sector' => $sector,
                'userReservations' => $userReservations
            ])->render();
    
            // Print text using writeHTMLCell method
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    
            // Output PDF
            return $pdf->Output('sector_reservation_report.pdf', 'I'); // 'I' will display in the browser
        } else {
            return redirect()->back()->with('error', 'No sector found with this ID');
        }
    }
    
    
}

