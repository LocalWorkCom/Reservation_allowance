<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservationAllowance;
use App\Models\User;
use App\Models\departements;
use App\Models\grade;
use App\Models\UserGrade;
use App\Models\Sector;
use TCPDF;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReserveFetchController extends Controller
{
    public function static()
    {
        return view('reservation_fetch.index');
    }

   
    private function fetchUser($fileNumber)
    {
        $query = User::query()->where('file_number', $fileNumber);
    
        $manager = Auth::user();
    
        if ($manager->rule_id == 3) { // Department Manager
            $accessibleDepartments = array_merge(
                [$manager->department_id],
                $this->getManagerAccessibleDepartments($manager->department_id)
            );
            $query->whereIn('department_id', $accessibleDepartments);
        }
    
        if ($manager->rule_id == 4) { // Sector Manager
            $accessibleDepartments = $this->getSectorDepartments($manager->sector);
            $query->where(function ($q) use ($manager, $accessibleDepartments) {
                $q->where('sector', $manager->sector)
                  ->orWhereIn('department_id', $accessibleDepartments);
            });
        }
    
        return $query->first();
    }

    private function getManagerAccessibleDepartments($departmentId)
    {
        return departements::where('parent_id', $departmentId)->pluck('id')->toArray();
    }

    private function getSectorDepartments($sectorId)
    {
        return departements::where('sector_id', $sectorId)->pluck('id')->toArray();
    }

    private function canAccessEmployeeData($manager, $employee)
    {
        if ($manager->rule_id == 2) {
            return true; // Super Admin: Access to all users
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
    

    private function addCommonColumns($dataTable)
    {
        return $dataTable
            ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
            ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
            ->addColumn('grade', function ($row)  {
                $latestUserGrade = UserGrade::where('user_id', $row->user_id)
                    ->orderBy('created_at', 'desc')
                    ->with('grade')
                    ->first();
                 
                return $latestUserGrade && $latestUserGrade->grade
                    ? $latestUserGrade->grade->name
                    : 'N/A';
            })
            ->addColumn('name', fn($row) => optional($row->user)->name ?? 'N/A')
            ->addColumn('department', fn($row) => optional($row->departements)->name ?? 'N/A')
            ->addColumn('sector', fn($row) => optional($row->sector)->name ?? 'N/A')
            
            
            ->addColumn('type', fn($row) => $row->type == 1 ? 'حجز كلي' : 'حجز جزئي')
            ->addColumn('amount', fn($row) => number_format($row->amount, 2) . ' د ك')
            ->addColumn('created_by', function ($row) {
                $creator = User::find($row->created_by);
                return $creator ? $creator->name : 'غير معروف';
            })
            ->addColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('Y-m-d H:i:s'))
     
            ->addIndexColumn();
    }
    


    private function fetchReservations($userId, $startDate, $endDate)
    {
        return ReservationAllowance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    }

    private function handleReservationData($employee, $reservations)
    {
        $totalAmount = $reservations->sum('amount'); 
        return $this->addCommonColumns(DataTables::of($reservations))
            ->with([
                'totalAmount' => number_format($totalAmount, 2) . ' د ك',
            ])
            ->make(true);
    }
    


    public function getAll(Request $request)
    {
        $fileNumber = $request->input('file_number'); 
        $employee = $this->fetchUser($fileNumber);
    
        if ($employee && $this->canAccessEmployeeData(Auth::user(), $employee)) {
            $reservations = ReservationAllowance::where('user_id', $employee->id)
                ->with(['user', 'departements', 'sector']) 
                ->get();
    
            return $this->handleReservationData($employee, $reservations);
        }
    
        return $this->userNotFoundOrUnauthorizedResponse();
    }
    
    
    

    private function getReservationsWithinDays(Request $request, $days)
    {
        $fileNumber = $request->input('file_number'); 
        $employee = $this->fetchUser($fileNumber);
    
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
        $fileNumber = $request->input('file_number');
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        if (!$fileNumber || !$startDate || !$endDate) {
            return response()->json(['error' => 'Invalid input provided.'], 400);
        }
    
        $employee = $this->fetchUser($fileNumber); 
        if (!$employee) {
            return response()->json(['error' => 'Employee not found or unauthorized.'], 403);
        }
    
        if ($employee && $this->canAccessEmployeeData(Auth::user(), $employee)) {
            $reservations = ReservationAllowance::where('user_id', $employee->id)
                ->with(['user', 'departements', 'sector']) 
                ->get();
    
            return $this->handleReservationData($employee, $reservations);
        }
    
        return $this->userNotFoundOrUnauthorizedResponse();
    }
    
    
    

    private function userNotFoundOrUnauthorizedResponse()
    {
        return response()->json([
            'draw' => 1, 
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ]);
    }

    public function printReport(Request $request)
{
    $user = $this->fetchUser($request->input('file_number'));
    if (!$user || !$this->canAccessEmployeeData(Auth::user(), $user)) {
        return redirect()->back()->with('error', 'No user found with this File Number');
    }

    // Fetch reservations with related departments
    $reservations = ReservationAllowance::where('user_id', $user->id)
        ->with('departements')
        ->get();

    // Total calculations
    $totalAmount = $reservations->sum(fn($item) => (float) $item->amount);
    $totalFullReservation = $reservations->where('type', 1)->sum(fn($item) => (float) $item->amount);
    $totalPartialReservation = $reservations->where('type', 2)->sum(fn($item) => (float) $item->amount);

    // Fetch the latest grade based on reservation dates
    $latestGrade = UserGrade::where('user_id', $user->id)
        ->where('created_at', '<=', $reservations->max('date')) 
        ->orderBy('created_at', 'desc')
        ->with('grade')
        ->first();

    $data = [
        'reservations' => $reservations,
        'user' => $user,
        'sector' => Sector::find($user->sector)->name ?? 'N/A',
        'department' => departements::find($user->department_id)->name ?? 'N/A',
        'grade' => $latestGrade?->grade?->name ?? 'N/A', 
        'totalAmount' => number_format((float) $totalAmount, 2) . ' د ك',
        'totalFullReservation' => number_format((float) $totalFullReservation, 2) . ' د ك',
        'totalPartialReservation' => number_format((float) $totalPartialReservation, 2) . ' د ك',
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
