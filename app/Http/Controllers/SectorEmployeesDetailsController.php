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
    public function index(Request $request, $sectorUuid)
    {
        if (auth()->check() && auth()->user()->rule_id == 2) {
            $sector = Sector::where('uuid', $sectorUuid)->first();
            $month = $request->input('month');
            $year = $request->input('year');

            return view('sector_employees.index', [
                'sectorId' => $sectorUuid,
                'sectorName' => $sector ? $sector->name : 'Unknown Sector',
                'month' => $month,
                'year' => $year,
            ]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }
    
    public function getData($sectorUuid, Request $request)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }

        $month = $request->input('month');
        $year = $request->input('year');

        $employees = User::whereIn('id', function ($query) use ($sector, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('sector_id', $sector->id)
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
            ->addColumn('days', function ($user) use ($sector, $month, $year) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                            ->where('sector_id', $sector->id)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->where('type', 1)
                            ->count();

                $partialDays = ReservationAllowance::where('user_id', $user->id)
                               ->where('sector_id', $sector->id)
                               ->whereYear('date', $year)
                               ->whereMonth('date', $month)
                               ->where('type', 2)
                               ->count();

                return "كلي: $fullDays | جزئي: $partialDays | مجموع: " . ($fullDays + $partialDays);
            })
            ->addColumn('allowance', function ($user) use ($sector, $month, $year) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                                 ->where('sector_id', $sector->id)
                                 ->whereYear('date', $year)
                                 ->whereMonth('date', $month)
                                 ->where('type', 1)
                                 ->sum('amount');

                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                    ->where('sector_id', $sector->id)
                                    ->whereYear('date', $year)
                                    ->whereMonth('date', $month)
                                    ->where('type', 2)
                                    ->sum('amount');

                return "كلي: " . number_format($fullAllowance, 2) . " د.ك | جزئي: " . number_format($partialAllowance, 2) . " د.ك | مجموع: " . number_format($fullAllowance + $partialAllowance, 2) . " د.ك";
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function notReservedUsers(Request $request, $sectorUuid)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return abort(404, 'Sector not found');
        }

        $month = $request->input('month');
        $year = $request->input('year');

        return view('sector_employees.not_reserved', [
            'sectorId' => $sectorUuid,
            'sectorName' => $sector->name,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function getNotReservedData(Request $request, $sectorUuid)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }

        $month = $request->input('month');
        $year = $request->input('year');

        $users = User::where('sector_uuid', $sectorUuid)
            ->whereNotIn('id', function ($query) use ($sector, $month, $year) {
                $query->select('user_id')
                    ->from('reservation_allowances')
                    ->where('sector_id', $sector->id)
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
    public function sectorUsersPage(Request $request, $sectorUuid)
    {
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return abort(404, 'Sector not found');
        }
    
        $month = $request->input('month');
        $year = $request->input('year');
    
        return view('sector_employees.sector_users', [
            'sectorId' => $sectorUuid, 
            'sectorName' => $sector->name,
            'month' => $month,
            'year' => $year,
        ]);
    }
    
    public function getSectorUsers(Request $request, $sectorUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
    
        if (!$month || !$year) {
            return response()->json([
                'data' => [],
                'error' => 'Month and Year are required.',
            ]);
        }
    
        $sector = Sector::where('uuid', $sectorUuid)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }
    
        // Fetch users belonging to the sector
        $users = User::where('sector_uuid', $sectorUuid) 
            ->with(['grade', 'department']) 
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


public function printReport($sectorUuid, Request $request)
{
    $sector = Sector::where('uuid', $sectorUuid)->first();
    if (!$sector) {
        return abort(404, 'Sector not found');
    }

    $month = $request->input('month');
    $year = $request->input('year');

    $employees = User::whereIn('id', function ($query) use ($sector, $month, $year) {
            $query->select('user_id')
                  ->from('reservation_allowances')
                  ->where('sector_id', $sector->id)
                  ->whereYear('date', $year)
                  ->whereMonth('date', $month);
        })
        ->with(['grade', 'department'])
        ->get();

    $userReservations = $employees->map(function ($user) use ($sector, $month, $year) {
        $fullDays = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', 1)
                    ->count();

        $partialDays = ReservationAllowance::where('user_id', $user->id)
                       ->where('sector_id', $sector->id)
                       ->whereYear('date', $year)
                       ->whereMonth('date', $month)
                       ->where('type', 2)
                       ->count();

        return [
            'file_number' => $user->file_number,
            'user' => $user,
            'department' => $user->department->name ?? 'N/A',
            'grade' => $user->grade->name ?? 'N/A',
            'fullDays' => $fullDays,
            'partialDays' => $partialDays,
            'totalDays' => $fullDays + $partialDays,
            'fullAllowance' => ReservationAllowance::where('user_id', $user->id)
                             ->where('sector_id', $sector->id)
                             ->whereYear('date', $year)
                             ->whereMonth('date', $month)
                             ->where('type', 1)
                             ->sum('amount'),
            'partialAllowance' => ReservationAllowance::where('user_id', $user->id)
                                ->where('sector_id', $sector->id)
                                ->whereYear('date', $year)
                                ->whereMonth('date', $month)
                                ->where('type', 2)
                                ->sum('amount'),
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

public function allowanceDetailsPage(Request $request, $employeeUuid)
{
    $month = $request->input('month');
    $year = $request->input('year');
    $employee = User::where('uuid', $employeeUuid)->first();

    if (!$employee) {
        return abort(404, 'Employee not found');
    }

    return view('sector_employees.allowance_details', [
        'employeeName' => $employee->name,
        'employeeUuid' => $employeeUuid, 
        'month' => $month,
        'year' => $year,
    ]);
}

public function getAllowanceDetails(Request $request, $employeeUuid)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $employee = User::where('uuid', $employeeUuid)->first();
    if (!$employee) {
        return response()->json(['error' => 'Employee not found'], 404);
    }

    $allowances = ReservationAllowance::where('user_id', $employee->id) 
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