<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use App\Models\ReservationAllowance;
use Yajra\DataTables\Facades\DataTables;
use TCPDF;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\grade;
use App\Models\Sector;
use Illuminate\Support\Facades\Log;

class ReservationReportController extends Controller
{
    public function index()
    {

        if ( auth()->user()->rule_id == 2) {
        return view('reserv_report.index');
    } else {
        return abort(403, 'Unauthorized action.');
    }
    }

   

    public function getReportData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        if (!$startDate || !$endDate) {
            return response()->json([
                'data' => [],
                'totalSectors' => 0,
                'totalDepartments' => 0,
                'totalUsers' => 0,
                'totalAmount' => '0 د.ك'
            ]);
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Group data by sector within the selected date range
        $query = ReservationAllowance::whereBetween('date', [$start, $end])
            ->selectRaw('sector_id, COUNT(DISTINCT user_id) as user_count, SUM(amount) as total_amount')
            ->groupBy('sector_id')
            ->having('total_amount', '>', 0);

        // Prepare DataTable
        $data = DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sector_name', function ($row) {
                return Sector::find($row->sector_id)->name ?? 'N/A';
            })
            ->addColumn('main_departments_count', function ($row) {
                return departements::where('sector_id', $row->sector_id)
                    ->whereNull('parent_id')
                    ->count();
            })
            ->addColumn('sub_departments_count', function ($row) {
                $mainDepartments = departements::where('sector_id', $row->sector_id)
                    ->whereNull('parent_id')
                    ->pluck('id');

                return departements::where('sector_id', $row->sector_id)
                    ->whereIn('parent_id', $mainDepartments)
                    ->count();
            })
            ->addColumn('employee_count', function ($row) {
                return $row->user_count;
            })
            ->addColumn('total_amount', function ($row) {
                return number_format($row->total_amount, 2) . ' د.ك';
            })
            ->make(true);

        // Calculate summary data
        $totalSectors = $query->count(); // Number of sectors with reservations in the selected date range
        $totalDepartments = departements::whereIn('sector_id', $query->pluck('sector_id'))
            ->distinct()
            ->count();
        $totalUsers = $query->get()->sum('user_count');
        $totalAmount = number_format($query->get()->sum('total_amount'), 2) . ' د.ك';

        // Adding totals to the JSON response
        $response = $data->getData(true);
        $response['totalSectors'] = $totalSectors;
        $response['totalDepartments'] = $totalDepartments;
        $response['totalUsers'] = $totalUsers;
        $response['totalAmount'] = $totalAmount;

        return response()->json($response);
    }
    
    public function printReport(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        // Get data grouped by sector within the selected date range
        $data = ReservationAllowance::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('sector_id, COUNT(DISTINCT user_id) as user_count, SUM(amount) as total_amount')
            ->groupBy('sector_id')
            ->having('total_amount', '>', 0)
            ->get()
            ->map(function ($item) {
                // Add sector name, main and sub-departments count
                $item->sector_name = Sector::find($item->sector_id)->name ?? 'N/A';
    
                // Count main departments
                $mainDepartments = departements::where('sector_id', $item->sector_id)
                    ->whereNull('parent_id')
                    ->get();
                $item->main_departments_count = $mainDepartments->count();
    
                // Count sub-departments
                $item->sub_departments_count = departements::where('sector_id', $item->sector_id)
                    ->whereIn('parent_id', $mainDepartments->pluck('id'))
                    ->count();
    
                return $item;
            });
    
        $totalSectors = $data->count();
        $totalDepartments = departements::whereIn('sector_id', $data->pluck('sector_id'))
            ->distinct()
            ->count();
        $totalUsers = $data->sum('user_count');
        $totalAmount = number_format($data->sum('total_amount'), 2) . ' د.ك';
    
        
        // Pass data to the Blade template
        $pdf = new TCPDF();
        $pdf->SetCreator('Your App');
        $pdf->SetTitle('تقارير بدل حجز');
        $pdf->AddPage();
        $pdf->setRTL(true);
        $pdf->SetFont('dejavusans', '', 12);
    
        // Render HTML content with Blade view
        $html = view('reserv_report.pdf', compact(
            'data', 'totalSectors', 'totalDepartments', 'totalUsers', 'totalAmount', 'startDate', 'endDate'
        ))->render();
        
        $pdf->writeHTML($html, true, false, true, false, '');
    
        return $pdf->Output('reserv_report.pdf', 'I');
    }
    
    

    
public function showDepartmentDetails(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $department = departements::find($departmentId);
    $employees = ReservationAllowance::where('departement_id', $departmentId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user.grade')
        ->get()
        ->groupBy('user_id')
        ->map(function ($entries) {
            $totalAmount = $entries->sum('amount');
            $totalDays = $entries->count();
            return [
                'user' => $entries->first()->user,
                'total_days' => $totalDays,
                'total_amount' => $totalAmount,
            ];
        });

    return view('reserv_report.department_details', compact('department', 'employees', 'startDate', 'endDate'));
}

public function getDepartmentDetailsData(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $reservations = ReservationAllowance::where('departement_id', $departmentId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user') // Ensure 'user' relationship is loaded
        ->get();

    return DataTables::of($reservations)
        ->addIndexColumn() // This auto-generates the row number
        ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
        ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
        ->addColumn('name', fn($row) => optional($row->user)->name ?? 'Unknown') 
        ->addColumn('department', fn($row) => optional($row->user->department)->name ?? 'N/A')
        ->addColumn('type', fn($row) => $row->type == 1 ? 'حجز كلي' : 'حجز جزئي')
        ->addColumn('amount', fn($row) => number_format($row->amount, 2) . ' د ك')
        ->make(true);
}

public function printDepartmentDetails(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    // Fetch data for the selected department and date range
    $department = departements::find($departmentId);
    $reservations = ReservationAllowance::where('departement_id', $departmentId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user')
        ->get();

    // Prepare the PDF data
    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل بدل حجز لموظفي إدارة {$department->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    // Pass the data to a view and render it as HTML for the PDF
    $html = view('reserv_report.department_details_pdf', compact('reservations', 'department', 'startDate', 'endDate'))->render();
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("department_details_report_{$department->name}.pdf", 'I');
}

public function showSectorDetails(Request $request, $sectorId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $sector = Sector::find($sectorId);
    $employees = ReservationAllowance::where('sector_id', $sectorId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user.grade')
        ->get()
        ->groupBy('user_id')
        ->map(function ($entries) {
            $totalAmount = $entries->sum('amount');
            $totalDays = $entries->count();
            return [
                'user' => $entries->first()->user,
                'total_days' => $totalDays,
                'total_amount' => $totalAmount,
            ];
        });

    return view('reserv_report.sector_details', compact('sector', 'employees', 'startDate', 'endDate'));
}

public function getSectorDetailsData(Request $request, $sectorId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $reservations = ReservationAllowance::where('sector_id', $sectorId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user') // Ensure 'user' relationship is loaded
        ->get();

    return DataTables::of($reservations)
        ->addIndexColumn()
        ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
        ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
        ->addColumn('name', fn($row) => optional($row->user)->name ?? 'Unknown') 
        ->addColumn('department', fn($row) => optional($row->user->department)->name ?? 'N/A')
        ->addColumn('type', fn($row) => $row->type == 1 ? 'حجز كلي' : 'حجز جزئي')
        ->addColumn('amount', fn($row) => number_format($row->amount, 2) . ' د ك')
        ->make(true);
}

public function printSectorDetails(Request $request, $sectorId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    // Fetch data for the selected sector and date range
    $sector = Sector::find($sectorId);
    $reservations = ReservationAllowance::where('sector_id', $sectorId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user')
        ->get();

    // Prepare the PDF data
    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل بدل حجز لموظفي قطاع {$sector->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    // Pass the data to a view and render it as HTML for the PDF
    $html = view('reserv_report.sector_details_pdf', compact('reservations', 'sector', 'startDate', 'endDate'))->render();
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("sector_details_report_{$sector->name}.pdf", 'I');
}

    
}
