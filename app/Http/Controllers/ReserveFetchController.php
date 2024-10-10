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
use Illuminate\Support\Facades\Auth;

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

    // Get all sub-department IDs for a department
    private function getManagerAccessibleDepartments($departmentId)
    {
        return departements::where('parent_id', $departmentId)->pluck('id')->toArray();
    }

    // Get all departments under a sector
    private function getSectorDepartments($sectorId)
    {
        return departements::where('sector_id', $sectorId)->pluck('id')->toArray();
    }

    // Check access to employee data based on manager role
    private function canAccessEmployeeData($manager, $employee)
    {
        if ($manager->rule_id == 2) {
            return true; // Super Admin access
        }

        if ($manager->rule_id == 3) { // Department Manager
            $accessibleDepartments = array_merge(
                [$manager->department_id],
                $this->getManagerAccessibleDepartments($manager->department_id)
            );
            return in_array($employee->department_id, $accessibleDepartments);
        }

        if ($manager->rule_id == 4) { // Sector Manager
            $accessibleDepartments = $this->getSectorDepartments($manager->sector);
            return $employee->sector == $manager->sector || in_array($employee->department_id, $accessibleDepartments);
        }

        return false;
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
            ->addColumn('amount', fn($row) => number_format($row->amount, 2) . ' د ك')
            ->addIndexColumn();
    }

    // Get Grade Type
    private function getGradeType($user)
    {
        return match (grade::find($user->grade_id)?->type ?? null) {
            1 => 'فرد', 2 => 'ظابط', 3 => 'مهني', default => 'N/A',
        };
    }

    // Fetch reservation data for a user within a date range
    private function fetchReservations($userId, $startDate, $endDate)
    {
        return ReservationAllowance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    }

    // Main reservation data response handler with manager restrictions
    private function handleReservationData($employee, $reservations)
    {
        $totalAmount = $reservations->sum('amount');
        return $this->addCommonColumns(DataTables::of($reservations), $employee)
            ->with([
                'totalAmount' => number_format($totalAmount, 2) . ' د ك',
            ])
            ->make(true);
    }

    public function getAll(Request $request)
    {
        $civilNumber = $request->input('civil_number');
        $employee = $this->fetchUser($civilNumber);

        if ($employee && $this->canAccessEmployeeData(Auth::user(), $employee)) {
            $reservations = ReservationAllowance::where('user_id', $employee->id)->get();
            return $this->handleReservationData($employee, $reservations);
        }
        return $this->userNotFoundOrUnauthorizedResponse();
    }

    // Handle reservation data for date ranges
    private function getReservationsWithinDays(Request $request, $days)
    {
        $civilNumber = $request->input('civil_number');
        $employee = $this->fetchUser($civilNumber);

        if ($employee && $this->canAccessEmployeeData(Auth::user(), $employee)) {
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $reservations = $this->fetchReservations($employee->id, $startDate, $endDate);
            return $this->handleReservationData($employee, $reservations);
        }
        return $this->userNotFoundOrUnauthorizedResponse();
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

        if ($user && $this->canAccessEmployeeData(Auth::user(), $user) && $startDate && $endDate) {
            $reservations = $this->fetchReservations($user->id, $startDate, $endDate);
            return $this->handleReservationData($user, $reservations);
        }
        return $this->userNotFoundOrUnauthorizedResponse();
    }

    // Custom response for user not found or unauthorized access
    private function userNotFoundOrUnauthorizedResponse()
    {
        return response()->json([
            'draw' => 1, // Ensures that DataTables processes the response correctly
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ]);
    }

    public function printReport(Request $request)
    {
        $user = $this->fetchUser($request->input('civil_number'));
        if (!$user || !$this->canAccessEmployeeData(Auth::user(), $user)) {
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
