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
    
        // Convert dates to Carbon instances
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
    
        // Query to group by department and calculate totals
        $query = ReservationAllowance::whereBetween('date', [$start, $end])
            ->whereNotNull('departement_id') // Exclude records with NULL department_id
            ->selectRaw('departement_id, SUM(amount) as total_amount, COUNT(DISTINCT user_id) as user_count')
            ->groupBy('departement_id');
    
        $data = DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('department_name', function ($row) {
                return departements::find($row->departement_id)->name ?? 'N/A';
            })
            ->make(true);
    
        // Calculating distinct department count and other totals
        $totalDepartments = $query->distinct('departement_id')->count('departement_id'); // Ensuring unique departments
        $totalUsers = $query->get()->sum('user_count');
        $totalAmount = number_format($query->get()->sum('total_amount'));
    
        // Adding totals to the JSON response
        $response = $data->getData(true);
        $response['totalDepartments'] = $totalDepartments;
        $response['totalUsers'] = $totalUsers;
        $response['totalAmount'] = $totalAmount;
    
        return response()->json($response);
    }
    
    public function printReport(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        // Get department data within the selected date range
        $data = ReservationAllowance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('departement_id') // Exclude records with NULL department_id
            ->selectRaw('departement_id, SUM(amount) as total_amount, COUNT(DISTINCT user_id) as user_count')
            ->groupBy('departement_id')
            ->get()
            ->map(function ($item) {
                // Find and add department name to each item
                $item->department_name = departements::find($item->departement_id)->name ?? 'N/A';
                return $item;
            });
    
        $totalDepartments = $data->count();
        $totalUsers = $data->sum('user_count');
        $totalAmount = number_format($data->sum('total_amount'), 2) . ' د.ك';
    
        // Generate the PDF content
        $pdf = new TCPDF();
        $pdf->SetCreator('Your App');
        $pdf->SetTitle('تقارير بدل حجز');
        $pdf->AddPage();
        $pdf->setRTL(true);
        $pdf->SetFont('dejavusans', '', 12);
    
        $html = view('reserv_report.pdf', compact('data', 'totalDepartments', 'totalUsers', 'totalAmount', 'startDate', 'endDate'))->render();
        $pdf->writeHTML($html, true, false, true, false, '');
    
        return $pdf->Output('reservation_report.pdf', 'I');
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
    
}
