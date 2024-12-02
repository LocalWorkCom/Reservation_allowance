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
use App\Models\UserGrade;
use Illuminate\Support\Facades\Log;

class ReservationReportController extends Controller
{
    public function index()
    {

        if (auth()->check() && in_array(auth()->user()->rule_id, [2, 4])) {
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
    
            // Filter sectors based on user rule_id
            if (auth()->user()->rule_id == 2) { // Super Admin
                $sectors = Sector::pluck('id');
            } elseif (auth()->user()->rule_id == 4) { // Sector Manager
                $sectors = Sector::where('id', auth()->user()->sector)->pluck('id');
            } else {
                return response()->json([
                    'error' => 'Unauthorized action.'
                ], 403);
            }
    
            // Query reservation allowances for the filtered sectors
            $query = ReservationAllowance::whereBetween('date', [$start, $end])
                ->whereIn('sector_id', $sectors)
                ->selectRaw('sector_id, COUNT(DISTINCT user_id) as user_count, SUM(amount) as total_amount')
                ->groupBy('sector_id')
                ->having('total_amount', '>', 0);
    
            $data = DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('sector_name', function ($row) {
                    $sector = Sector::find($row->sector_id);
                    return $sector ? $sector->name : 'N/A';
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
                ->addColumn('uuid', function ($row) {
                    $sector = Sector::find($row->sector_id);
                    return $sector ? $sector->uuid : null; // Return UUID for frontend linking
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
        try {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
            // Filter sectors based on user role
            if (auth()->user()->rule_id == 2) { // Super Admin
                $sectors = Sector::pluck('id');
            } elseif (auth()->user()->rule_id == 4) { // Sector Manager
                $sectors = Sector::where('id', auth()->user()->sector)->pluck('id');
            } else {
                abort(403, 'Unauthorized action.');
            }
    
            $data = ReservationAllowance::whereBetween('date', [$startDate, $endDate])
                ->whereIn('sector_id', $sectors)
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
    
            // Calculate totals
            $totalSectors = $data->count();
            $totalDepartments = departements::whereIn('sector_id', $data->pluck('sector_id'))
                ->distinct()
                ->count();
            $totalUsers = $data->sum('user_count');
            $totalAmount = number_format($data->sum('total_amount'), 2) . ' د.ك';
    
            // Initialize PDF
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
        } catch (\Exception $e) {
            \Log::error("Error generating report PDF: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate the report.');
        }
    }
    
    
    public function showUserDetails(Request $request, $userUuid)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        $user = User::where('uuid', $userUuid)->firstOrFail(); // Find user by UUID
        $reservations = ReservationAllowance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    
        return view('reserv_report.user_details', compact('user', 'reservations', 'startDate', 'endDate'));
    }
    
    
    public function printUserDetails(Request $request, $userUuid)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $user = User::where('uuid', $userUuid)->firstOrFail(); 

    $reservations = ReservationAllowance::where('user_id', $user->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->map(function ($reservation) {
            return [
                'day' => Carbon::parse($reservation->date)->translatedFormat('l'),
                'date' => Carbon::parse($reservation->date)->format('Y-m-d'),
                'type' => $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي',
                'amount' => number_format($reservation->amount, 2) . ' د.ك',
            ];
        });

    // Initialize the PDF
    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل الحجز للموظف: {$user->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    // Render the HTML content
    $html = view('reserv_report.user_details_pdf', compact('user', 'reservations', 'startDate', 'endDate'))->render();
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("user_details_{$user->name}.pdf", 'I');
}

    public function getUserDetailsData(Request $request, $userId)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        $reservations = ReservationAllowance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    
        return DataTables::of($reservations)
            ->addIndexColumn()
            ->addColumn('day', fn($row) => Carbon::parse($row->date)->translatedFormat('l'))
            ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('Y-m-d'))
            ->addColumn('type', fn($row) => $row->type == 1 ? 'حجز كلي' : 'حجز جزئي')
            ->addColumn('amount', fn($row) => number_format($row->amount, 2) . ' د ك')
            ->make(true);
    }
    
    
    public function showSectorDetails(Request $request, $sectorId)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        $sector = Sector::where('uuid', $sectorId)->first();
        if (!$sector) {
            return abort(404, 'Sector not found');
        }
    
        $employees = ReservationAllowance::where('sector_id', $sector->id)
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
    
        $sector = Sector::where('uuid', $sectorId)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }
    
        $employees = User::whereIn('id', function ($query) use ($sector, $startDate, $endDate) {
                $query->select('user_id')
                    ->from('reservation_allowances')
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate]);
            })
            ->with(['department'])
            ->get();
    
        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', function ($user) use ($startDate, $endDate) {
                $latestGrade = UserGrade::where('user_id', $user->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->with('grade') 
                    ->first();
    
                return $latestGrade?->grade?->name ?? 'N/A'; 
            })
            ->addColumn('department', fn($user) => $user->department->name ?? 'N/A')
            ->addColumn('full_days', function ($user) use ($sector, $startDate, $endDate) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 1)
                    ->count();
            })
            ->addColumn('partial_days', function ($user) use ($sector, $startDate, $endDate) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 2)
                    ->count();
            })
            ->addColumn('total_days', function ($user) use ($sector, $startDate, $endDate) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 1)
                    ->count();
                $partialDays = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 2)
                    ->count();
    
                return $fullDays + $partialDays;
            })
            ->addColumn('full_allowance', function ($user) use ($sector, $startDate, $endDate) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 1)
                    ->sum('amount');
            })
            ->addColumn('partial_allowance', function ($user) use ($sector, $startDate, $endDate) {
                return ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 2)
                    ->sum('amount');
            })
            ->addColumn('total_allowance', function ($user) use ($sector, $startDate, $endDate) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 1)
                    ->sum('amount');
    
                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                    ->where('sector_id', $sector->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('type', 2)
                    ->sum('amount');
    
                return $fullAllowance + $partialAllowance;
            })
            ->make(true);
    }
    
    


    public function printSectorDetails(Request $request, $sectorId)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        $sector = Sector::where('uuid', $sectorId)->first();
        if (!$sector) {
            return response()->json(['error' => 'Sector not found'], 404);
        }
    
        $reservations = ReservationAllowance::where('sector_id', $sector->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['user.department'])
            ->get()
            ->map(function ($reservation) use ($startDate, $endDate) {
                $latestGrade = UserGrade::where('user_id', $reservation->user_id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->with('grade')
                    ->first();
    
                return [
                    'day' => \Carbon\Carbon::parse($reservation->date)->translatedFormat('l'),
                    'date' => \Carbon\Carbon::parse($reservation->date)->format('Y-m-d'),
                    'name' => $reservation->user->name ?? 'N/A',
                    'file_number' => $reservation->user->file_number ?? 'N/A',
                    'grade' => $latestGrade?->grade?->name ?? 'N/A', // Fetch latest grade
                    'department' => $reservation->user->department->name ?? 'N/A',
                    'type' => $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي',
                    'reservation_amount' => number_format($reservation->amount, 2),
                ];
            })
            ->sortBy('grade');
    
        // Prepare the PDF
        $pdf = new TCPDF();
        $pdf->SetCreator('Your App');
        $pdf->SetTitle("تفاصيل بدل حجز لموظفي قطاع {$sector->name}");
        $pdf->AddPage();
        $pdf->setRTL(true);
        $pdf->SetFont('dejavusans', '', 12);
    
        // Render HTML content with Blade view
        $html = view('reserv_report.sector_details_pdf', compact('reservations', 'sector', 'startDate', 'endDate'))->render();
        $pdf->writeHTML($html, true, false, true, false, '');
    
        return $pdf->Output("sector_details_report_{$sector->name}.pdf", 'I');
    }
    

    


////

public function showMainDepartmentDetails(Request $request, $sectorId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

  $sector = Sector::where('uuid', $sectorId)->first();
        if (!$sector) {
            return abort(404, 'Sector not found');
        }    
    $mainDepartments = departements::where('sector_id', $sector->id)
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
                'uuid' => $department->uuid, 
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

    $sector = Sector::where('uuid', $sectorId)->first();
    if (!$sector) {
        return abort(404, 'Sector not found');
    }    

    $mainDepartments = departements::where('sector_id', $sector->id)
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
                'reservation_amount' => number_format($totalAmount, 2) . ' د.ك',
            ];
        });

    // Prepare the PDF data
    $pdf = new TCPDF();
    $pdf->SetCreator('Your App');
    $pdf->SetTitle("تفاصيل الإدارات الرئيسية للقطاع {$sector->name}");
    $pdf->AddPage();
    $pdf->setRTL(true);
    $pdf->SetFont('dejavusans', '', 12);

    // Pass the data to a view and render it as HTML for the PDF
    $html = view('reserv_report.main_departments_details_pdf', compact('mainDepartments', 'sector', 'startDate', 'endDate'))->render();
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("main_departments_report_{$sector->name}.pdf", 'I');
}



//
public function showSubDepartments(Request $request, $departmentUuid)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $mainDepartment = departements::where('uuid', $departmentUuid)->first(); 
    if (!$mainDepartment) {
        return abort(404, 'Main department not found');
    }

    $subDepartments = departements::where('parent_id', $mainDepartment->id)
        ->get()
        ->map(function ($subDepartment) use ($startDate, $endDate) {
            $employeeCount = ReservationAllowance::where('departement_id', $subDepartment->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

            // $totalAmount = ReservationAllowance::where('departement_id', $subDepartment->id)
            //     ->whereBetween('date', [$startDate, $endDate])
            //     ->sum('amount');
            $fullDays = ReservationAllowance::where('departement_id', $subDepartment->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('type', 1)
            ->count();

        $partialDays = ReservationAllowance::where('departement_id', $subDepartment->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('type', 2)
            ->count();

        $fullAllowance = ReservationAllowance::where('departement_id', $subDepartment->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('type', 1)
            ->sum('amount');

        $partialAllowance = ReservationAllowance::where('departement_id', $subDepartment->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('type', 2)
            ->sum('amount');

        $totalDays = $fullDays + $partialDays;
        $totalAllowance = $fullAllowance + $partialAllowance;

            return [
                'uuid' => $subDepartment->uuid, 
                'sub_department_name' => $subDepartment->name,
                'employee_count' => $employeeCount,
                'full_days' => $fullDays,
                'partial_days' => $partialDays,
                'total_days' => $totalDays,
                'full_allowance' => number_format($fullAllowance, 2) . ' د.ك',
                'partial_allowance' => number_format($partialAllowance, 2) . ' د.ك',
                'total_allowance' => number_format($totalAllowance, 2) . ' د.ك',
            ];
        });

    return view('reserv_report.sub_departments_details', compact('mainDepartment', 'subDepartments', 'startDate', 'endDate'));
}

public function printSubDepartmentsDetails(Request $request, $departmentUuid)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $mainDepartment = departements::where('uuid', $departmentUuid)->first(); // Use uuid
    if (!$mainDepartment) {
        return abort(404, 'Main department not found');
    }

    $subDepartments = departements::where('parent_id', $mainDepartment->id)
        ->get()
        ->map(function ($subDepartment) use ($startDate, $endDate) {
            $employeeCount = ReservationAllowance::where('departement_id', $subDepartment->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

            $totalAmount = ReservationAllowance::where('departement_id', $subDepartment->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

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

    $html = view('reserv_report.sub_departments_details_pdf', compact(
        'mainDepartment', 'subDepartments', 'startDate', 'endDate'
    ))->render();
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output("sub_departments_details_{$mainDepartment->name}.pdf", 'I');
}


public function showMainDepartmentEmployees(Request $request, $departmentId) 
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    // Fetch the department using UUID
    $department = departements::where('uuid', $departmentId)->first();
    if (!$department) {
        return abort(404, 'Department not found');
    }

    return view('reserv_report.main_department_employees', compact('department', 'startDate', 'endDate'));
}

public function getMainDepartmentEmployeesData(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $department = departements::where('uuid', $departmentId)->first();
    if (!$department) {
        return response()->json(['error' => 'Department not found'], 404);
    }

    $employees = User::whereIn('id', function ($query) use ($department, $startDate, $endDate) {
            $query->select('user_id')
                ->from('reservation_allowances')
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate]);
        })
        ->with(['department'])
        ->get();

    return DataTables::of($employees)
        ->addColumn('file_number', fn($user) => $user->file_number)
        ->addColumn('name', fn($user) => $user->name)
        ->addColumn('grade', function ($user) use ($startDate, $endDate) {
            $latestGrade = UserGrade::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->with('grade') // Eager load the related grade
                ->first();

            return $latestGrade?->grade?->name ?? 'N/A'; // Return the grade name or 'N/A'
        })
        ->addColumn('full_days', function ($user) use ($department, $startDate, $endDate) {
            return ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 1)
                ->count();
        })
        ->addColumn('partial_days', function ($user) use ($department, $startDate, $endDate) {
            return ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 2)
                ->count();
        })
        ->addColumn('total_days', function ($user) use ($department, $startDate, $endDate) {
            $fullDays = ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 1)
                ->count();
            $partialDays = ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 2)
                ->count();
            return $fullDays + $partialDays;
        })
        ->addColumn('full_allowance', function ($user) use ($department, $startDate, $endDate) {
            return ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 1)
                ->sum('amount');
        })
        ->addColumn('partial_allowance', function ($user) use ($department, $startDate, $endDate) {
            return ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 2)
                ->sum('amount');
        })
        ->addColumn('total_allowance', function ($user) use ($department, $startDate, $endDate) {
            $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 1)
                ->sum('amount');

            $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                ->where('departement_id', $department->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('type', 2)
                ->sum('amount');

            return $fullAllowance + $partialAllowance;
        })
        ->addIndexColumn()
        ->make(true);
}






public function printMainDepartmentEmployees(Request $request, $departmentId)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $department = departements::where('uuid', $departmentId)->first(); // Use uuid for lookup
    if (!$department) {
        return abort(404, 'Department not found');
    }

    $employees = ReservationAllowance::where('departement_id', $department->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user')
        ->get()
        ->map(function ($entry) use ($startDate, $endDate) {
            $latestGrade = UserGrade::where('user_id', $entry->user_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->with('grade') 
                ->first();

            return [
                'day' => \Carbon\Carbon::parse($entry->date)->translatedFormat('l'),
                'date' => \Carbon\Carbon::parse($entry->date)->format('Y-m-d'),
                'name' => $entry->user->name,
                'file_number' => $entry->user->file_number,
                'grade' => $latestGrade?->grade?->name ?? 'N/A', 
                'type' => $entry->type == 1 ? 'حجز كلي' : 'حجز جزئي',
                'reservation_amount' => number_format($entry->amount, 2),
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
public function showSubDepartmentEmployees(Request $request, $subDepartmentUuid)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $subDepartment = departements::where('uuid', $subDepartmentUuid)->first();
    if (!$subDepartment) {
        return abort(404, 'Sub-department not found');
    }

    $employees = ReservationAllowance::where('departement_id', $subDepartment->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user') 
        ->get()
        ->map(function ($reservation) use ($startDate, $endDate) {
            $latestGrade = UserGrade::where('user_id', $reservation->user_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->with('grade')
                ->first();

            return [
                'day' => Carbon::parse($reservation->date)->translatedFormat('l'),
                'date' => Carbon::parse($reservation->date)->format('Y-m-d'),
                'employee_name' => optional($reservation->user)->name ?? 'Unknown',
                'file_number' => optional($reservation->user)->file_number ?? 'N/A',
                'grade' => $latestGrade?->grade?->name ?? 'N/A', 
                'type' => $reservation->type == 1 ? 'حجز كلي' : 'حجز جزئي',
                'reservation_amount' => number_format($reservation->amount, 2) . ' د.ك',
            ];
        });

    return view('reserv_report.sub_department_employees', compact('subDepartment', 'employees', 'startDate', 'endDate'));
}




public function printSubDepartmentEmployees(Request $request, $subDepartmentUuid)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

    $subDepartment = departements::where('uuid', $subDepartmentUuid)->first();
    if (!$subDepartment) {
        return abort(404, 'Sub-department not found');
    }

    $employees = ReservationAllowance::where('departement_id', $subDepartment->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->with('user') 
        ->get()
        ->map(function ($reservation) use ($startDate, $endDate) {
            $latestGrade = UserGrade::where('user_id', $reservation->user_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->with('grade')
                ->first();

            return [
                'day' => Carbon::parse($reservation->date)->translatedFormat('l'),
                'date' => Carbon::parse($reservation->date)->format('Y-m-d'),
                'name' => optional($reservation->user)->name ?? 'Unknown',
                'file_number' => optional($reservation->user)->file_number ?? 'N/A',
                'grade' => $latestGrade?->grade?->name ?? 'N/A', 
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
        'subDepartmentUuid' => $subDepartmentUuid,
        'start_date' => $request->input('start_date'),
        'end_date' => $request->input('end_date'),
    ]);

    return $pdf->Output("sub_department_employees_{$subDepartment->name}.pdf", 'I');
}


}
