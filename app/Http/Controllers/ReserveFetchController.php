<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservationAllowance;
use App\Models\User;
use App\Models\departements;
use TCPDF;

use Carbon\Carbon;


use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class ReserveFetchController extends Controller
{
    public function static()
    {
        return view('reservation_fetch.index'); 
    }

    public function getAll(Request $request)
    {
        $civilNumber = $request->input('civil_number'); // رقم الهوية
        $startDate = $request->input('start_date'); // Optional start date
        $endDate = $request->input('end_date'); // Optional end date

        // First, fetch the user by Civil_number
        $user = User::where('Civil_number', $civilNumber)->first();

        if ($user) {
            // Create query for ReservationAllowance based on user_id and optional date range
            $query = ReservationAllowance::where('user_id', $user->id);

            if ($startDate && $endDate) {
                // Filter by date range if provided
                $query->whereBetween('date', [$startDate, $endDate]);
            }

            // Fetch the reservations
            $reservations = $query->get();

            return DataTables::of($reservations)
                ->addColumn('day', function($row) {
                    return Carbon::parse($row->date)->translatedFormat('l'); // Get day name in Arabic
                })
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->date)->format('Y-m-d'); // Get formatted date
                })
                ->addColumn('name', function() use ($user) {
                    return $user->name; // User name from the User model
                })
                ->addColumn('department', function($row) {
                    // Get the department name based on the department_id for each record
                    $department = departements::find($row->departement_id);
                    return $department ? $department->name : 'N/A'; // Department name
                })
                ->addColumn('type', function($row) {
                    return $row->type == 1 ? 'حجز كلي' : 'حجز جزئي'; // Reservation type (1: Full, 2: Partial)
                })
                ->addColumn('amount', function($row) {
                    return number_format($row->amount, 2); // Format the amount
                })
                ->addIndexColumn() // Auto numbering for "الترتيب"
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

    public function printReport(Request $request)
    {
        $civilNumber = $request->input('civil_number');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        // Fetch the user by Civil_number
        $user = User::where('Civil_number', $civilNumber)->first();
    
        if ($user) {
            // Create query for ReservationAllowance based on user_id and optional date range
            $query = ReservationAllowance::where('user_id', $user->id);
    
            if ($startDate && $endDate) {
                // Filter by date range if provided
                $query->whereBetween('date', [$startDate, $endDate]);
            }
    
            // Fetch the reservations with department relationship
            $reservations = $query->with('departements')->get();
    
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
