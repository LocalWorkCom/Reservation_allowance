<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservationAllowance;
use App\Models\User;
use App\Models\departements;
use App\Models\grade;
use App\Models\Sector;
use TCPDF;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class ReserveFetchController extends Controller
{
    public function static()
    {
        return view('reservation_fetch.index');
    }

    // Fetch user by civil number
    private function fetchUser($civilNumber)
    {
        return User::where('Civil_number', $civilNumber)->first();
    }

    // Common columns for DataTables response
    private function addCommonColumns($dataTable, $user)
    {
        return $dataTable
            ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
            ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
            ->addColumn('name', fn() => $user->name)
            ->addColumn('department', fn($row) => departements::find($row->departement_id)->name ?? 'N/A')
            ->addColumn('grade_type', fn() => $this->getGradeType($user))
            ->addColumn('grade', fn() => grade::find($user->grade_id)->name ?? 'N/A')
            ->addColumn('sector', fn() => Sector::find($user->sector)->name ?? 'N/A')
            ->addColumn('type', fn($row) => $row->type == 1 ? 'حجز كلي' : 'حجز جزئي')
            ->addColumn('amount', function ($row) {
                return number_format($row->amount, 2) . ' د ك'; 
            })
            ->addIndexColumn();
    }

    // Get Grade Type
    private function getGradeType($user)
    {
        return match (grade::find($user->grade_id)?->type ?? null) {
            1 => 'فرد', 2 => 'ظابط', 3 => 'مهني', default => 'N/A',
        };
    }

    // Common method to fetch reservation data based on date range
    private function fetchReservations($userId, $startDate, $endDate)
    {
        return ReservationAllowance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate]);
    }

    // Main reservation data response handler
    private function handleReservationData($user, $reservations)
    {
        $totalAmount = $reservations->sum('amount');
        return $this->addCommonColumns(DataTables::of($reservations), $user)
        ->with([
            'totalAmount' => number_format($totalAmount, 2) . ' د ك', 
        ])            ->make(true);
    }

    public function getAll(Request $request)
    {
        $user = $this->fetchUser($request->input('civil_number'));
        if ($user) {
            $reservations = ReservationAllowance::where('user_id', $user->id);
            return $this->handleReservationData($user, $reservations);
        }
        return $this->userNotFoundResponse();
    }

    public function getLastMonth(Request $request)
    {
        return $this->getReservationsWithinDays($request, 30);
    }

    public function getLastThreeMonths(Request $request)
    {
        return $this->getReservationsWithinDays($request, 90);
    }

    public function getLastSixMonths(Request $request)
    {
        return $this->getReservationsWithinDays($request, 180);
    }

    public function getLastYear(Request $request)
    {
        return $this->getReservationsWithinDays($request, 365);
    }

    public function getCustomDateRange(Request $request)
    {
        $user = $this->fetchUser($request->input('civil_number'));
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        if ($user && $startDate && $endDate) {
            $reservations = $this->fetchReservations($user->id, $startDate, $endDate);
            return $this->handleReservationData($user, $reservations);
        }
        return $this->userNotFoundResponse();
    }

    // Handle reservation data for date ranges
    private function getReservationsWithinDays(Request $request, $days)
    {
        $user = $this->fetchUser($request->input('civil_number'));
        if ($user) {
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $reservations = $this->fetchReservations($user->id, $startDate, $endDate);
            return $this->handleReservationData($user, $reservations);
        }
        return $this->userNotFoundResponse();
    }

    // Response for user not found
    private function userNotFoundResponse()
    {
        return response()->json([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'No user found with this Civil Number'
        ]);
    }

    public function printReport(Request $request)
    {
        $user = $this->fetchUser($request->input('civil_number'));
        if (!$user) {
            return redirect()->back()->with('error', 'No user found with this Civil Number');
        }

        $reservations = ReservationAllowance::where('user_id', $user->id)->with('departements')->get();
        $totalAmount = $reservations->sum('amount');
        $data = [
            'reservations' => $reservations,
            'user' => $user,
            'sector' => Sector::find($user->sector)->name ?? 'N/A',
            'department' => departements::find($user->department_id)->name ?? 'N/A',
            'grade' => grade::find($user->grade_id)->name ?? 'N/A',
            'gradeType' => $this->getGradeType($user),
            'totalAmount' => $totalAmount,
            'totalFullReservation' => $reservations->where('type', 1)->sum('amount'),
            'totalPartialReservation' => $reservations->where('type', 2)->sum('amount'),
        ];

        // Generate PDF
        $pdf = $this->generatePDF($data);
        return $pdf->Output('reservation_report.pdf', 'I');
    }

    // Generate PDF with given data
    private function generatePDF($data)
    {
        $pdf = new TCPDF();
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your App');
        $pdf->SetTitle('Reservation Report');
        $pdf->SetSubject('Report');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->AddPage();
        $pdf->setRTL(true);
        $html = view('reservation_fetch.pdf', $data)->render();
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        return $pdf;
    }
}
