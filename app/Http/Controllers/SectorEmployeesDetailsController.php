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
            $sector = Sector::where('uuid',$sectorId)->first();
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
    
    public function getData($sectorId, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $sector_id = Sector::where('uuid',$sectorId)->first()->id;

        $employees = User::whereIn('id', function ($query) use ($sector_id, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('sector_id', $sector_id)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
            ->with(['grade', 'department'])
            ->get();
    
        return DataTables::of($employees)
            ->addColumn('file_number', fn($user) => $user->file_number) 
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
            ->addColumn('department', fn($user) => $user->department->name ?? 'N/A')
            ->addColumn('days', function ($user) use ($sectorId, $month, $year) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                            ->where('sector_id', $sectorId)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->where('type', 1)
                            ->count();
    
                $partialDays = ReservationAllowance::where('user_id', $user->id)
                               ->where('sector_id', $sectorId)
                               ->whereYear('date', $year)
                               ->whereMonth('date', $month)
                               ->where('type', 2)
                               ->count();
    
                $totalDays = $fullDays + $partialDays;
                return "كلي: $fullDays | جزئي: $partialDays | مجموع: $totalDays";
            })
            ->addColumn('allowance', function ($user) use ($sectorId, $month, $year) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                                 ->where('sector_id', $sectorId)
                                 ->whereYear('date', $year)
                                 ->whereMonth('date', $month)
                                 ->where('type', 1)
                                 ->sum('amount');
    
                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                    ->where('sector_id', $sectorId)
                                    ->whereYear('date', $year)
                                    ->whereMonth('date', $month)
                                    ->where('type', 2)
                                    ->sum('amount');
    
                $totalAllowance = $fullAllowance + $partialAllowance;
                return "كلي: " . number_format($fullAllowance, 2) . " د.ك | جزئي: " . number_format($partialAllowance, 2) . " د.ك | مجموع: " . number_format($totalAllowance, 2) . " د.ك";
            })
            ->addIndexColumn()
            ->make(true);
    }
    
    public function notReservedUsers(Request $request, $sectorId)
{
    $sector = Sector::find($sectorId);
    $month = $request->input('month');
    $year = $request->input('year');

    return view('sector_employees.not_reserved', [
        'sectorId' => $sectorId,
        'sectorName' => $sector ? $sector->name : 'Unknown Sector',
        'month' => $month,
        'year' => $year,
    ]);
}
public function getNotReservedData(Request $request, $sectorId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $users = User::where('sector', $sectorId)
        ->whereNotIn('id', function ($query) use ($sectorId, $month, $year) {
            $query->select('user_id')
                ->from('reservation_allowances')
                ->where('sector_id', $sectorId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month);
        })
        ->with(['grade', 'department'])
        ->get();

    return DataTables::of($users)
        ->addColumn('file_number', fn($user) => $user->file_number)
        ->addColumn('name', fn($user) => $user->name)
        ->addColumn('department', fn($user) => $user->department->name ?? 'N/A')
        ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
        ->addIndexColumn()
        ->make(true);
}
public function sectorUsersPage(Request $request, $sectorId)
{
    $sector = Sector::find($sectorId);
    $month = $request->input('month');
    $year = $request->input('year');

    return view('sector_employees.sector_users', [
        'sectorId' => $sectorId,
        'sectorName' => $sector ? $sector->name : 'Unknown Sector',
        'month' => $month,
        'year' => $year,
    ]);
}


public function getSectorUsers(Request $request, $sectorId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    // Ensure $month and $year are provided
    if (!$month || !$year) {
        return response()->json([
            'data' => [],
            'error' => 'Month and Year are required.'
        ]);
    }

    // Fetch users belonging to the sector
    $users = User::where('sector', $sectorId)
        ->with(['grade', 'department']) // Ensure these relationships exist
        ->get();

    // Return data to DataTables
    return DataTables::of($users)
        ->addColumn('file_number', fn($user) => $user->file_number)
        ->addColumn('name', fn($user) => $user->name)
        ->addColumn('department', fn($user) => $user->department->name ?? 'N/A')
        ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
        ->addIndexColumn()
        ->make(true);
}



    public function printReport($sectorId, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        Log::info("Print report for sectorId: $sectorId, month: $month, year: $year");
    
        if (!$month || !$year) {
            return redirect()->back()->withErrors('Please select a valid month and year.');
        }
    
        $sector = Sector::find($sectorId);
    
        $employees = User::whereIn('id', function ($query) use ($sectorId, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('sector_id', $sectorId)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
            ->with(['grade', 'department'])
            ->get();
    
        $userReservations = $employees->map(function ($user) use ($sectorId, $month, $year) {
            $fullDays = ReservationAllowance::where('user_id', $user->id)
                        ->where('sector_id', $sectorId)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->where('type', 1)
                        ->count();
    
            $partialDays = ReservationAllowance::where('user_id', $user->id)
                           ->where('sector_id', $sectorId)
                           ->whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->where('type', 2)
                           ->count();
    
            $totalDays = $fullDays + $partialDays;
    
            $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                             ->where('sector_id', $sectorId)
                             ->whereYear('date', $year)
                             ->whereMonth('date', $month)
                             ->where('type', 1)
                             ->sum('amount');
    
            $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                ->where('sector_id', $sectorId)
                                ->whereYear('date', $year)
                                ->whereMonth('date', $month)
                                ->where('type', 2)
                                ->sum('amount');
    
            $totalAllowance = $fullAllowance + $partialAllowance;
    
            return [
                'file_number' => $user->file_number, // Add file number
                'user' => $user,
                'department' => $user->department->name ?? 'N/A',
                'grade' => $user->grade->name ?? 'N/A',
                'fullDays' => $fullDays,
                'partialDays' => $partialDays,
                'totalDays' => $totalDays,
                'fullAllowance' => $fullAllowance,
                'partialAllowance' => $partialAllowance,
                'totalAllowance' => $totalAllowance,
            ];
        });
    
        $pdf = new TCPDF();
        $pdf->SetCreator('Your App');
        $pdf->SetTitle("تفاصيل الموظفين للقطاع: {$sector->name}");
        $pdf->AddPage();
        $pdf->setRTL(true);
        $pdf->SetFont('dejavusans', '', 12);
    
        $html = view('sector_employees.sector_employees_report', compact(
            'sector', 'userReservations', 'month', 'year'
        ))->render();
    
        $pdf->writeHTML($html, true, false, true, false, '');
    
        return $pdf->Output("sector_employees_{$sector->name}.pdf", 'I');
    }

    public function allowanceDetailsPage(Request $request, $employeeId)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $employee = User::find($employeeId);
    
        if (!$employee) {
            return abort(404, 'Employee not found');
        }
    
        return view('sector_employees.allowance_details', [
            'employeeName' => $employee->name,
            'employeeId' => $employeeId,
            'month' => $month,
            'year' => $year
        ]);
    }
    public function getAllowanceDetails(Request $request, $employeeId)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $allowances = ReservationAllowance::where('user_id', $employeeId)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->get();

    return DataTables::of($allowances)
        ->addColumn('date', function ($allowance) {
            return Carbon::parse($allowance->date)->format('Y-m-d');
        })
        ->addColumn('type', fn($allowance) => $allowance->type == 1 ? 'كلي' : 'جزئي')
        ->addColumn('amount', fn($allowance) => number_format($allowance->amount, 2) . ' د.ك')
        ->addIndexColumn()
        ->make(true);
}

    
    
}