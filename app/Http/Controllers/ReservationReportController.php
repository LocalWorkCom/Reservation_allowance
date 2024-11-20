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

        if (auth()->check() && auth()->user()->rule_id == 2) {
            return view('reserv_report.index');
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }

    public function getReportData(Request $request)
    {
        try {
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
    
            $query = ReservationAllowance::whereBetween('date', [$start, $end])
                ->selectRaw('sector_id, COUNT(DISTINCT user_id) as user_count, SUM(amount) as total_amount')
                ->groupBy('sector_id')
                ->having('total_amount', '>', 0);
    
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
    
            // Calculate totals for summary
            $totalSectors = $query->count();
            $totalDepartments = departements::whereIn('sector_id', $query->pluck('sector_id'))
                ->distinct()
                ->count();
            $totalUsers = $query->get()->sum('user_count');
            $totalAmount = number_format($query->get()->sum('total_amount'), 2) . ' د.ك';
    
            // Add summary data to the response
            $response = $data->getData(true);
            $response['totalSectors'] = $totalSectors;
            $response['totalDepartments'] = $totalDepartments;
            $response['totalUsers'] = $totalUsers;
            $response['totalAmount'] = $totalAmount;
    
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error("Error fetching report data: " . $e->getMessage());
    
            return response()->json([
                'data' => [],
                'totalSectors' => 0,
                'totalDepartments' => 0,
                'totalUsers' => 0,
                'totalAmount' => '0 د.ك',
                'error' => 'Failed to load data'
            ]);
        }
    }
    
    
    public function printReport(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        $data = ReservationAllowance::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('sector_id, COUNT(DISTINCT user_id) as user_count, SUM(amount) as total_amount')
            ->groupBy('sector_id')
            ->having('total_amount', '>', 0)
            ->get()
            ->map(function ($item) {
                $item->sector_name = Sector::find($item->sector_id)->name ?? 'N/A';
    
                $mainDepartments = departements::where('sector_id', $item->sector_id)
                    ->whereNull('parent_id')
                    ->get();
                $item->main_departments_count = $mainDepartments->count();
    
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
    
    

    
// public function showDepartmentDetails(Request $request, $departmentId)
// {
//     $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
//     $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

//     $department = departements::find($departmentId);
//     $employees = ReservationAllowance::where('departement_id', $departmentId)
//         ->whereBetween('date', [$startDate, $endDate])
//         ->with('user.grade')
//         ->get()
//         ->groupBy('user_id')
//         ->map(function ($entries) {
//             $totalAmount = $entries->sum('amount');
//             $totalDays = $entries->count();
//             return [
//                 'user' => $entries->first()->user,
//                 'total_days' => $totalDays,
//                 'total_amount' => $totalAmount,
//             ];
//         });

//     return view('reserv_report.department_details', compact('department', 'employees', 'startDate', 'endDate'));
// }

// public function getDepartmentDetailsData(Request $request, $departmentId)
// {
//     $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
//     $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

//     $reservations = ReservationAllowance::where('departement_id', $departmentId)
//         ->whereBetween('date', [$startDate, $endDate])
//         ->with('user') // Ensure 'user' relationship is loaded
//         ->get();

//     return DataTables::of($reservations)
//         ->addIndexColumn() // This auto-generates the row number
//         ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
//         ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
//         ->addColumn('name', fn($row) => optional($row->user)->name ?? 'Unknown') 
//         ->addColumn('department', fn($row) => optional($row->user->department)->name ?? 'N/A')
//         ->addColumn('type', fn($row) => $row->type == 1 ? 'حجز كلي' : 'حجز جزئي')
//         ->addColumn('amount', fn($row) => number_format($row->amount, 2) . ' د ك')
//         ->make(true);
// }

// public function printDepartmentDetails(Request $request, $departmentId)
// {
//     $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
//     $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

//     // Fetch data for the selected department and date range
//     $department = departements::find($departmentId);
//     $reservations = ReservationAllowance::where('departement_id', $departmentId)
//         ->whereBetween('date', [$startDate, $endDate])
//         ->with('user')
//         ->get();

//     // Prepare the PDF data
//     $pdf = new TCPDF();
//     $pdf->SetCreator('Your App');
//     $pdf->SetTitle("تفاصيل بدل حجز لموظفي إدارة {$department->name}");
//     $pdf->AddPage();
//     $pdf->setRTL(true);
//     $pdf->SetFont('dejavusans', '', 12);

//     // Pass the data to a view and render it as HTML for the PDF
//     $html = view('reserv_report.department_details_pdf', compact('reservations', 'department', 'startDate', 'endDate'))->render();
//     $pdf->writeHTML($html, true, false, true, false, '');

//     return $pdf->Output("department_details_report_{$department->name}.pdf", 'I');
// }
// ///
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
        ->with(['user.grade', 'user.department'])
        ->get();

    return DataTables::of($reservations)
        ->addIndexColumn()
        ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
        ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
        ->addColumn('name', fn($row) => optional($row->user)->name ?? 'Unknown')
        ->addColumn('file_number', fn($row) => optional($row->user)->file_number ?? 'N/A')
        ->addColumn('grade', fn($row) => optional($row->user->grade)->name ?? 'N/A') // Add grade
        ->addColumn('department', fn($row) => optional($row->user->department)->name ?? 'N/A')
        ->addColumn('type', fn($row) => $row->type == 1 ? 'حجز كلي' : 'حجز جزئي')
        ->addColumn('amount', fn($row) => number_format($row->amount, 2) . ' د ك')
        ->make(true);
}

public function printSectorDetails(Request $request, $sectorId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $sector = Sector::find($sectorId);
    $reservations = ReservationAllowance::where('sector_id', $sectorId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with(['user.grade', 'user.department']) 
        ->get()
        ->sortBy(function ($reservation) {
            return optional($reservation->user->grade)->name; 
        });

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


////

public function showMainDepartmentDetails(Request $request, $sectorId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $sector = Sector::find($sectorId);
    
    $mainDepartments = departements::where('sector_id', $sectorId)
        ->whereNull('parent_id')
        ->get()
        ->map(function ($department) use ($startDate, $endDate) {
            $subDepartmentsCount = departements::where('parent_id', $department->id)->count();
            $employeeCount = ReservationAllowance::where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
            
            $totalAmount = ReservationAllowance::where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount'); 

            return [
                'id' => $department->id,
                'department_name' => $department->name,
                'sub_departments_count' => $subDepartmentsCount,
                'employee_count' => $employeeCount,
                'reservation_amount' => number_format($totalAmount, 2) . ' د.ك'
            ];
        });

    return view('reserv_report.main_departments_details', compact('sector', 'mainDepartments', 'startDate', 'endDate'));
}


public function printMainDepartmentDetails(Request $request, $sectorId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $sector = Sector::find($sectorId);

    // Fetch main departments with sub-department, employee counts, and reservation amount
    $mainDepartments = departements::where('sector_id', $sectorId)
        ->whereNull('parent_id')
        ->get()
        ->map(function ($department) use ($startDate, $endDate) {
            $subDepartmentsCount = departements::where('parent_id', $department->id)->count();
            $employeeCount = ReservationAllowance::where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
                
            $totalAmount = ReservationAllowance::where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount'); 
            return [
                'department_name' => $department->name,
                'sub_departments_count' => $subDepartmentsCount,
                'employee_count' => $employeeCount,
                'reservation_amount' => number_format($totalAmount, 2) . ' د.ك'
            ];
        });

    // Initialize PDF with TCPDF
    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل الإدارات الرئيسية للقطاع: {$sector->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    // Render HTML content with Blade view
    $html = view('reserv_report.main_departments_details_pdf', compact(
        'sector', 'mainDepartments', 'startDate', 'endDate'
    ))->render();

    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("main_departments_details_{$sector->name}.pdf", 'I');
}


//
public function showSubDepartments(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $mainDepartment = departements::find($departmentId);
    
    $subDepartments = departements::where('parent_id', $departmentId)
        ->get()
        ->map(function ($subDepartment) use ($startDate, $endDate) {
            $employeeCount = ReservationAllowance::where('departement_id', $subDepartment->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

            $totalAmount = ReservationAllowance::where('departement_id', $subDepartment->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount'); 

            return [
                'id' => $subDepartment->id, 
                'sub_department_name' => $subDepartment->name,
                'employee_count' => $employeeCount,
                'reservation_amount' => number_format($totalAmount, 2) . ' د.ك'
            ];
        });

    return view('reserv_report.sub_departments_details', compact('mainDepartment', 'subDepartments', 'startDate', 'endDate'));
}


public function printSubDepartmentsDetails(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $mainDepartment = departements::find($departmentId);
    
    // Retrieve sub-departments with employee counts and reservation amount
    $subDepartments = departements::where('parent_id', $departmentId)
        ->get()
        ->map(function ($subDepartment) use ($startDate, $endDate) {
            $employeeCount = ReservationAllowance::where('departement_id', $subDepartment->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

            $totalAmount = ReservationAllowance::where('departement_id', $subDepartment->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount'); // Calculate the reservation amount

            return [
                'sub_department_name' => $subDepartment->name,
                'employee_count' => $employeeCount,
                'reservation_amount' => number_format($totalAmount, 2) . ' د.ك'
            ];
        });

    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل الإدارات الفرعية للإدارة الرئيسية: {$mainDepartment->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    $html = view('reserv_report.sub_departments_details_pdf', compact('mainDepartment', 'subDepartments', 'startDate', 'endDate'))->render();
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("sub_departments_details_{$mainDepartment->name}.pdf", 'I');
}


public function showMainDepartmentEmployees(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $department = departements::find($departmentId);

    $employees = ReservationAllowance::where('departement_id', $departmentId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user.grade')
        ->get()
        ->map(function ($entry) {
            return [
                'day' => \Carbon\Carbon::parse($entry->date)->translatedFormat('l'), 
                'date' => \Carbon\Carbon::parse($entry->date)->format('Y-m-d'), 
                'name' => $entry->user->name,
                'file_number' => $entry->user->file_number,
                'grade' => optional($entry->user->grade)->name,
                'type' => $entry->type == 1 ? 'حجز كلي' : 'حجز جزئي', 
                'reservation_amount' => number_format($entry->amount, 2)
            ];
        });

    return view('reserv_report.main_department_employees', compact('department', 'employees', 'startDate', 'endDate'));
}

public function printMainDepartmentEmployees(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $department = departements::find($departmentId);

    $employees = ReservationAllowance::where('departement_id', $departmentId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user.grade')
        ->get()
        ->map(function ($entry) {
            return [
                'day' => \Carbon\Carbon::parse($entry->date)->translatedFormat('l'), 
                'date' => \Carbon\Carbon::parse($entry->date)->format('Y-m-d'), 
                'name' => $entry->user->name,
                'file_number' => $entry->user->file_number,
                'grade' => optional($entry->user->grade)->name,
                'type' => $entry->type == 1 ? 'حجز كلي' : 'حجز جزئي', 
                'reservation_amount' => number_format($entry->amount, 2)
            ];
        });

    // Set up the PDF
    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل الموظفين المحجوزين في الإدارة الرئيسية: {$department->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    // Render HTML content with Blade view
    $html = view('reserv_report.main_department_employees_pdf', compact(
        'department', 'employees', 'startDate', 'endDate'
    ))->render();
    
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("main_department_employees_report_{$department->name}.pdf", 'I');
}


////
public function showSubDepartmentEmployees(Request $request, $subDepartmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $subDepartment = departements::find($subDepartmentId);

    $employees = ReservationAllowance::where('departement_id', $subDepartmentId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user.grade') 
        ->get()
        ->map(function ($reservation) {
            return [
                'day' => Carbon::parse($reservation->date)->translatedFormat('l'),
                'date' => Carbon::parse($reservation->date)->format('Y-m-d'),
                'employee_name' => $reservation->user->name ?? 'Unknown',
                'file_number' => $reservation->user->file_number ?? 'N/A',
                'grade' => optional($reservation->user->grade)->name ?? 'N/A',
                'type' => $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي',
                'reservation_amount' => number_format($reservation->amount, 2),
            ];
        });

    return view('reserv_report.sub_department_employees', compact('subDepartment', 'employees', 'startDate', 'endDate'));
}






public function printSubDepartmentEmployees(Request $request, $subDepartmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $subDepartment = departements::find($subDepartmentId);

    $employees = ReservationAllowance::where('departement_id', $subDepartmentId)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user.grade') 
        ->get()
        ->map(function ($reservation) {
            return [
                'day' => Carbon::parse($reservation->date)->translatedFormat('l'),
                'date' => Carbon::parse($reservation->date)->format('Y-m-d'),
                'name' => optional($reservation->user)->name ?? 'Unknown',
                'file_number' => optional($reservation->user)->file_number ?? 'N/A',
                'grade' => optional($reservation->user->grade)->name ?? 'N/A',
                'type' => $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي',
                'amount' => number_format($reservation->amount, 2) . ' د.ك',
            ];
        });

    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل الموظفين للإدارة الفرعية: {$subDepartment->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    $html = view('reserv_report.sub_department_employees_pdf', compact(
        'subDepartment', 'employees', 'startDate', 'endDate'
    ))->render();
    $pdf->writeHTML($html, true, false, true, false, '');
    Log::info('Print route accessed', [
        'subDepartmentId' => $subDepartmentId,
        'start_date' => $request->input('start_date'),
        'end_date' => $request->input('end_date'),
    ]);
    
    return $pdf->Output("sub_department_employees_{$subDepartment->name}.pdf", 'I');
}


}
