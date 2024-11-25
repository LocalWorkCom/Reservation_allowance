<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\departements;
use App\Models\ReservationAllowance;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use TCPDF;

class DepartmentEmployeesDetailsController extends Controller
{
    public function index(Request $request, $departmentUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $department = departements::where('uuid', $departmentUuid)->first();

        return view('reserv_department_employees.index', [
            'departmentId' => $departmentUuid, // Pass UUID to the view
            'departmentName' => $department ? $department->name : 'Unknown Department',
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function getData($departmentUuid, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $department = departements::where('uuid', $departmentUuid)->first();
        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $employees = User::whereIn('id', function ($query) use ($department, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('departement_id', $department->id) // Use numeric ID internally
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
            ->with(['grade'])
            ->get();

        return DataTables::of($employees)
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
            ->addColumn('days', function ($user) use ($department, $month, $year) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                            ->where('departement_id', $department->id)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->where('type', 1)
                            ->count();

                $partialDays = ReservationAllowance::where('user_id', $user->id)
                               ->where('departement_id', $department->id)
                               ->whereYear('date', $year)
                               ->whereMonth('date', $month)
                               ->where('type', 2)
                               ->count();

                return "كلي: $fullDays | جزئي: $partialDays | مجموع: " . ($fullDays + $partialDays);
            })
            ->addColumn('allowance', function ($user) use ($department, $month, $year) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                                 ->where('departement_id', $department->id)
                                 ->whereYear('date', $year)
                                 ->whereMonth('date', $month)
                                 ->where('type', 1)
                                 ->sum('amount');

                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                    ->where('departement_id', $department->id)
                                    ->whereYear('date', $year)
                                    ->whereMonth('date', $month)
                                    ->where('type', 2)
                                    ->sum('amount');

                return "كلي: " . number_format($fullAllowance, 2) . " د.ك | جزئي: " . number_format($partialAllowance, 2) . " د.ك | مجموع: " . number_format($fullAllowance + $partialAllowance, 2) . " د.ك";
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function printReport($departmentUuid, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $department = departements::where('uuid', $departmentUuid)->first();
        if (!$department) {
            return abort(404, 'Department not found');
        }

        $employees = User::whereIn('id', function ($query) use ($department, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('departement_id', $department->id)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
            ->with(['grade'])
            ->get();

        $userReservations = $employees->map(function ($user) use ($department, $month, $year) {
            $fullDays = ReservationAllowance::where('user_id', $user->id)
                        ->where('departement_id', $department->id)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->where('type', 1)
                        ->count();

            $partialDays = ReservationAllowance::where('user_id', $user->id)
                           ->where('departement_id', $department->id)
                           ->whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->where('type', 2)
                           ->count();

            $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                             ->where('departement_id', $department->id)
                             ->whereYear('date', $year)
                             ->whereMonth('date', $month)
                             ->where('type', 1)
                             ->sum('amount');

            $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                ->where('departement_id', $department->id)
                                ->whereYear('date', $year)
                                ->whereMonth('date', $month)
                                ->where('type', 2)
                                ->sum('amount');

            return [
                'file_number' => $user->file_number,
                'name' => $user->name,
                'grade' => $user->grade->name ?? 'N/A',
                'fullDays' => $fullDays,
                'partialDays' => $partialDays,
                'totalAllowance' => $fullAllowance + $partialAllowance,
            ];
        });

        $pdf = new TCPDF();
        $pdf->SetCreator('Your App');
        $pdf->SetTitle("تفاصيل الموظفين للإدارة: {$department->name}");
        $pdf->AddPage();
        $pdf->setRTL(true);
        $pdf->SetFont('dejavusans', '', 12);

        $html = view('department_employees.report', compact(
            'department',
            'userReservations',
            'month',
            'year'
        ))->render();

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf->Output("department_employees_{$department->name}.pdf", 'I');
    }

    public function allowanceDetailsPage(Request $request, $employeeUuid)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $employee = User::where('uuid', $employeeUuid)->first();
    
        if (!$employee) {
            return abort(404, 'Employee not found');
        }
    
        return view('reservation_statics.allowance_details', [
            'employeeName' => $employee->name,
            'employeeUuid' => $employeeUuid, // Pass UUID to the view
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
            ->addColumn('date', fn($allowance) => Carbon::parse($allowance->date)->format('Y-m-d'))
            ->addColumn('type', fn($allowance) => $allowance->type == 1 ? 'كلي' : 'جزئي')
            ->addColumn('amount', fn($allowance) => number_format($allowance->amount, 2) . ' د.ك')
            ->addIndexColumn()
            ->make(true);
    }
}
