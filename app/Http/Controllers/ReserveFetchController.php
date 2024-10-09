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

    public function getAll(Request $request)
    {
        $civilNumber = $request->input('civil_number'); // رقم الهوية

        // Fetch the user by Civil_number
        $user = User::where('Civil_number', $civilNumber)->first();

        if ($user) {
            // Base query for ReservationAllowance based on user_id
            $reservations = ReservationAllowance::where('user_id', $user->id);
            $totalAmount = $reservations->sum('amount');
           
            return DataTables::of($reservations)
                ->addColumn('day', function ($row) {
                    return Carbon::parse($row->date)->translatedFormat('l'); // Get day name in Arabic
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('Y-m-d'); // Get formatted date
                })
                ->addColumn('name', function () use ($user) {
                    return $user->name; // User name from the User model
                })
                ->addColumn('department', function ($row) {
                    // Get the department name based on the department_id for each record
                    $department = departements::find($row->departement_id);
                    return $department ? $department->name : 'N/A'; // Department name
                })
                ->addColumn('grade_type', function () use ($user) {
                    // Fetch the grade type based on the grade_id from the grades table
                    $grade = Grade::find($user->grade_id);
                    if ($grade) {
                        switch ($grade->type) {
                            case 1:
                                return 'فرد';
                            case 2:
                                return 'ظابط';
                            case 3:
                                return 'مهني';
                            default:
                                return 'N/A';
                        }
                    }
                    return 'N/A';
                })
                ->addColumn('grade', function () use ($user) {
                    // Fetch the grade name based on the grade_id from the grades table
                    $grade = Grade::find($user->grade_id);
                    return $grade ? $grade->name : 'N/A'; // Grade name
                })
                ->addColumn('sector', function () use ($user) {
                    // Fetch the sector name based on the sector_id from the sectors table
                    $sector = Sector::find($user->sector);
                    return $sector ? $sector->name : 'N/A'; // Sector name
                })
                
                ->addColumn('type', function ($row) {
                    return $row->type == 1 ? 'حجز كلي' : 'حجز جزئي'; // Reservation type (1: Full, 2: Partial)
                })
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2); // Format the amount
                })
                ->addIndexColumn() // Auto numbering for "الترتيب"
                ->with([
                    'totalAmount' => number_format($totalAmount, 2),
                    
                ]) // Add totals to the response

                ->make(true);
        } else {
            // Return no data found message in JSON format
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'No user found with this Civil Number'
            ]);
        }
    }

    public function getLastMonth(Request $request)
    {
        $civilNumber = $request->input('civil_number'); // رقم الهوية

        // Fetch the user by Civil_number
        $user = User::where('Civil_number', $civilNumber)->first();

        if ($user) {
            // Calculate the date range for the last 30 days
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            Log::info('Fetching reservations for user: ' . $user->id);
            Log::info('Date range: ' . $startDate . ' to ' . $endDate);

            // Fetch reservations within the last 30 days for the user
            $reservations = ReservationAllowance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate]);

            Log::info('Number of reservations found: ' . $reservations->count());

            return DataTables::of($reservations)
                ->addColumn('day', function ($row) {
                    return Carbon::parse($row->date)->translatedFormat('l'); // Get day name in Arabic
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('Y-m-d'); // Get formatted date
                })
                ->addColumn('name', function () use ($user) {
                    return $user->name; // User name from the User model
                })
                ->addColumn('department', function ($row) {
                    // Get the department name based on the department_id for each record
                    $department = departements::find($row->departement_id);
                    return $department ? $department->name : 'N/A'; // Department name
                })
                ->addColumn('grade_type', function () use ($user) {
                    // Fetch the grade type based on the grade_id from the grades table
                    $grade = Grade::find($user->grade_id);
                    if ($grade) {
                        switch ($grade->type) {
                            case 1:
                                return 'فرد';
                            case 2:
                                return 'ظابط';
                            case 3:
                                return 'مهني';
                            default:
                                return 'N/A';
                        }
                    }
                    return 'N/A';
                })
                ->addColumn('grade', function () use ($user) {
                    // Fetch the grade name based on the grade_id from the grades table
                    $grade = Grade::find($user->grade_id);
                    return $grade ? $grade->name : 'N/A'; // Grade name
                })
                ->addColumn('sector', function () use ($user) {
                    // Fetch the sector name based on the sector_id from the sectors table
                    $sector = Sector::find($user->sector_id);
                    return $sector ? $sector->name : 'N/A'; // Sector name
                })
                
                ->addColumn('type', function ($row) {
                    return $row->type == 1 ? 'حجز كلي' : 'حجز جزئي'; // Reservation type (1: Full, 2: Partial)
                })
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2); // Format the amount
                })
                ->addIndexColumn() // Auto numbering for "الترتيب"
                ->with([
                    'totalAmount' => number_format($totalAmount, 2),
                   
                ]) // Add totals to the response

                ->make(true);
        } else {
            Log::warning('No user found with Civil Number: ' . $civilNumber);

            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'No user found with this Civil Number'
            ]);
        }
    }
    public function getLastThreeMonths(Request $request)
    {
        $civilNumber = $request->input('civil_number'); // رقم الهوية

        // Fetch the user by Civil_number
        $user = User::where('Civil_number', $civilNumber)->first();

        if ($user) {
            // Calculate the date range for the last 90 days
            $startDate = Carbon::now()->subDays(90)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            Log::info('Fetching reservations for user (Last 3 Months): ' . $user->id);
            Log::info('Date range: ' . $startDate . ' to ' . $endDate);

            // Fetch reservations within the last 90 days for the user
            $reservations = ReservationAllowance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate]);

            Log::info('Number of reservations found: ' . $reservations->count());

            return DataTables::of($reservations)
                ->addColumn('day', function ($row) {
                    return Carbon::parse($row->date)->translatedFormat('l'); // Get day name in Arabic
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('Y-m-d'); // Get formatted date
                })
                ->addColumn('name', function () use ($user) {
                    return $user->name; // User name from the User model
                })
                ->addColumn('department', function ($row) {
                    // Get the department name based on the department_id for each record
                    $department = departements::find($row->departement_id);
                    return $department ? $department->name : 'N/A'; // Department name
                })
                ->addColumn('type', function ($row) {
                    return $row->type == 1 ? 'حجز كلي' : 'حجز جزئي'; // Reservation type (1: Full, 2: Partial)
                })
                ->addColumn('grade_type', function () use ($user) {
                    // Fetch the grade type based on the grade_id from the grades table
                    $grade = Grade::find($user->grade_id);
                    if ($grade) {
                        switch ($grade->type) {
                            case 1:
                                return 'فرد';
                            case 2:
                                return 'ظابط';
                            case 3:
                                return 'مهني';
                            default:
                                return 'N/A';
                        }
                    }
                    return 'N/A';
                })
                ->addColumn('grade', function () use ($user) {
                    // Fetch the grade name based on the grade_id from the grades table
                    $grade = Grade::find($user->grade_id);
                    return $grade ? $grade->name : 'N/A'; // Grade name
                })
                ->addColumn('sector', function () use ($user) {
                    // Fetch the sector name based on the sector_id from the sectors table
                    $sector = Sector::find($user->sector_id);
                    return $sector ? $sector->name : 'N/A'; // Sector name
                })
                
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2); // Format the amount
                })
                ->addIndexColumn() // Auto numbering for "الترتيب"
                ->with([
                    'totalAmount' => number_format($totalAmount, 2),
                  
                ]) // Add totals to the response

                ->make(true);
        } else {
            Log::warning('No user found with Civil Number: ' . $civilNumber);

            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'No user found with this Civil Number'
            ]);
        }
    }


    public function getLastSixMonths(Request $request)
{
    $civilNumber = $request->input('civil_number'); // رقم الهوية

    // Fetch the user by Civil_number
    $user = User::where('Civil_number', $civilNumber)->first();

    if ($user) {
        // Calculate the date range for the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        Log::info('Fetching reservations for user (Last 6 Months): ' . $user->id);
        Log::info('Date range: ' . $startDate . ' to ' . $endDate);

        // Fetch reservations within the last 6 months for the user
        $reservations = ReservationAllowance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]);

        Log::info('Number of reservations found: ' . $reservations->count());

        return DataTables::of($reservations)
            ->addColumn('day', function ($row) {
                return Carbon::parse($row->date)->translatedFormat('l'); // Get day name in Arabic
            })
            ->addColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d'); // Get formatted date
            })
            ->addColumn('name', function () use ($user) {
                return $user->name; // User name from the User model
            })
            ->addColumn('department', function ($row) {
                // Get the department name based on the department_id for each record
                $department = departements::find($row->departement_id);
                return $department ? $department->name : 'N/A'; // Department name
            })
            ->addColumn('grade_type', function () use ($user) {
                // Fetch the grade type based on the grade_id from the grades table
                $grade = Grade::find($user->grade_id);
                if ($grade) {
                    switch ($grade->type) {
                        case 1:
                            return 'فرد';
                        case 2:
                            return 'ظابط';
                        case 3:
                            return 'مهني';
                        default:
                            return 'N/A';
                    }
                }
                return 'N/A';
            })
            ->addColumn('grade', function () use ($user) {
                // Fetch the grade name based on the grade_id from the grades table
                $grade = Grade::find($user->grade_id);
                return $grade ? $grade->name : 'N/A'; // Grade name
            })
            ->addColumn('sector', function () use ($user) {
                // Fetch the sector name based on the sector_id from the sectors table
                $sector = Sector::find($user->sector_id);
                return $sector ? $sector->name : 'N/A'; // Sector name
            })
            
            ->addColumn('type', function ($row) {
                return $row->type == 1 ? 'حجز كلي' : 'حجز جزئي'; // Reservation type (1: Full, 2: Partial)
            })
            ->addColumn('amount', function ($row) {
                return number_format($row->amount, 2); // Format the amount
            })
            ->addIndexColumn() // Auto numbering for "الترتيب"
            ->with([
                'totalAmount' => number_format($totalAmount, 2),
                
            ]) // Add totals to the response

            ->make(true);
    } else {
        Log::warning('No user found with Civil Number: ' . $civilNumber);

        return response()->json([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'No user found with this Civil Number'
        ]);
    }
}

public function getLastYear(Request $request)
{
    $civilNumber = $request->input('civil_number'); // رقم الهوية

    // Fetch the user by Civil_number
    $user = User::where('Civil_number', $civilNumber)->first();

    if ($user) {
        // Calculate the date range for the last year
        $startDate = Carbon::now()->subYear()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        Log::info('Fetching reservations for user (Last Year): ' . $user->id);
        Log::info('Date range: ' . $startDate . ' to ' . $endDate);

        // Fetch reservations within the last year for the user
        $reservations = ReservationAllowance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]);

        Log::info('Number of reservations found: ' . $reservations->count());

        return DataTables::of($reservations)
            ->addColumn('day', function ($row) {
                return Carbon::parse($row->date)->translatedFormat('l'); // Get day name in Arabic
            })
            ->addColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d'); // Get formatted date
            })
            ->addColumn('name', function () use ($user) {
                return $user->name; // User name from the User model
            })
            ->addColumn('department', function ($row) {
                // Get the department name based on the department_id for each record
                $department = departements::find($row->departement_id);
                return $department ? $department->name : 'N/A'; // Department name
            })
            ->addColumn('grade_type', function () use ($user) {
                // Fetch the grade type based on the grade_id from the grades table
                $grade = Grade::find($user->grade_id);
                if ($grade) {
                    switch ($grade->type) {
                        case 1:
                            return 'فرد';
                        case 2:
                            return 'ظابط';
                        case 3:
                            return 'مهني';
                        default:
                            return 'N/A';
                    }
                }
                return 'N/A';
            })
            ->addColumn('grade', function () use ($user) {
                // Fetch the grade name based on the grade_id from the grades table
                $grade = Grade::find($user->grade_id);
                return $grade ? $grade->name : 'N/A'; // Grade name
            })
            ->addColumn('sector', function () use ($user) {
                // Fetch the sector name based on the sector_id from the sectors table
                $sector = Sector::find($user->sector_id);
                return $sector ? $sector->name : 'N/A'; // Sector name
            })
            
            ->addColumn('type', function ($row) {
                return $row->type == 1 ? 'حجز كلي' : 'حجز جزئي'; // Reservation type (1: Full, 2: Partial)
            })
            ->addColumn('amount', function ($row) {
                return number_format($row->amount, 2); // Format the amount
            })
            ->addIndexColumn() // Auto numbering for "الترتيب"
            ->with([
                'totalAmount' => number_format($totalAmount, 2),
                
            ]) // Add totals to the response

            ->make(true);
    } else {
        Log::warning('No user found with Civil Number: ' . $civilNumber);

        return response()->json([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'No user found with this Civil Number'
        ]);
    }
}
public function getCustomDateRange(Request $request)
{
    $civilNumber = $request->input('civil_number'); // رقم الهوية
    $startDate = $request->input('start_date'); // Start date from request
    $endDate = $request->input('end_date'); // End date from request

    // Fetch the user by Civil_number
    $user = User::where('Civil_number', $civilNumber)->first();

    if ($user && $startDate && $endDate) {
        // Convert dates to Carbon instances
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Log custom date range search
        Log::info('Fetching reservations for user (Custom Date Range): ' . $user->id);
        Log::info('Date range: ' . $startDate . ' to ' . $endDate);

        // Fetch reservations within the custom date range for the user
        $reservations = ReservationAllowance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]);

        Log::info('Number of reservations found: ' . $reservations->count());

        return DataTables::of($reservations)
            ->addColumn('day', function ($row) {
                return Carbon::parse($row->date)->translatedFormat('l'); // Get day name in Arabic
            })
            ->addColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d'); // Get formatted date
            })
            ->addColumn('name', function () use ($user) {
                return $user->name; // User name from the User model
            })
            ->addColumn('department', function ($row) {
                // Get the department name based on the department_id for each record
                $department = departements::find($row->departement_id);
                return $department ? $department->name : 'N/A'; // Department name
            })
            ->addColumn('grade_type', function () use ($user) {
                // Fetch the grade type based on the grade_id from the grades table
                $grade = Grade::find($user->grade_id);
                if ($grade) {
                    switch ($grade->type) {
                        case 1:
                            return 'فرد';
                        case 2:
                            return 'ظابط';
                        case 3:
                            return 'مهني';
                        default:
                            return 'N/A';
                    }
                }
                return 'N/A';
            })
            ->addColumn('grade', function () use ($user) {
                // Fetch the grade name based on the grade_id from the grades table
                $grade = Grade::find($user->grade_id);
                return $grade ? $grade->name : 'N/A'; // Grade name
            })
            ->addColumn('sector', function () use ($user) {
                // Fetch the sector name based on the sector_id from the sectors table
                $sector = Sector::find($user->sector_id);
                return $sector ? $sector->name : 'N/A'; // Sector name
            })
            
            ->addColumn('type', function ($row) {
                return $row->type == 1 ? 'حجز كلي' : 'حجز جزئي'; // Reservation type (1: Full, 2: Partial)
            })
            ->addColumn('amount', function ($row) {
                return number_format($row->amount, 2); // Format the amount
            })
            ->addIndexColumn() // Auto numbering for "الترتيب"
            ->with([
                'totalAmount' => number_format($totalAmount, 2),
              
            ]) // Add totals to the response

            ->make(true);
    } else {
        Log::warning('No user found with Civil Number: ' . $civilNumber);

        return response()->json([
            'draw' => 0,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'No user found with this Civil Number or invalid date range'
        ]);
    }
}

public function printReport(Request $request)
{
    $civilNumber = $request->input('civil_number');

    // Fetch the user by Civil_number
    $user = User::where('Civil_number', $civilNumber)->first();

    if ($user) {
        // Fetch all reservations for the user and include department details
        $reservations = ReservationAllowance::where('user_id', $user->id)
            ->with('departements') // Ensure department details are included
            ->get();

        // Calculate the total amount
        $totalAmount = $reservations->sum('amount');
        $totalFullReservation = $reservations->where('type', 1)->sum('amount'); // Total for "حجز كلي"
        $totalPartialReservation = $reservations->where('type', 2)->sum('amount'); // Total for "حجز جزئي"

        // Fetch additional user details
        $sectorName = Sector::find($user->sector)?->name ?? 'N/A';
        $departmentName = departements::find($user->department_id)?->name ?? 'N/A';
        $gradeName = grade::find($user->grade_id)?->name ?? 'N/A';

        // Define grade type
        $gradeType = match (grade::find($user->grade_id)?->type ?? null) {
            1 => 'فرد',
            2 => 'ظابط',
            3 => 'مهني',
            default => 'N/A',
        };

        // Create a new TCPDF instance
        $pdf = new TCPDF();

        // Set document information
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your App');
        $pdf->SetTitle('Reservation Report');
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

        // Write HTML content
        $html = view('reservation_fetch.pdf', [
            'reservations' => $reservations,
            'user' => $user,
            'sector' => $sectorName,
            'department' => $departmentName,
            'grade' => $gradeName,
            'gradeType' => $gradeType,
            'totalAmount' => $totalAmount, // Pass the total amount to the view
            'totalFullReservation' => $totalFullReservation,
            'totalPartialReservation' => $totalPartialReservation,
        ])->render();

        // Print text using writeHTMLCell method
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // Output PDF
        return $pdf->Output('reservation_report.pdf', 'I'); // 'I' will display in the browser
    } else {
        return redirect()->back()->with('error', 'No user found with this Civil Number');
    }
}



}