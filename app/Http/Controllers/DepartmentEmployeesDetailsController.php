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
    public function index(Request $request, $departmentId)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $department = departements::find($departmentId);

        return view('reserv_department_employees.index', [
            'departmentId' => $departmentId,
            'departmentName' => $department ? $department->name : 'Unknown Department',
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function getData($departmentId, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $employees = User::whereIn('id', function ($query) use ($departmentId, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('departement_id', $departmentId)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
            ->with(['grade'])
            ->get();

        return DataTables::of($employees)
            ->addColumn('file_number', fn($user) => $user->file_number)
            ->addColumn('name', fn($user) => $user->name)
            ->addColumn('grade', fn($user) => $user->grade->name ?? 'N/A')
            ->addColumn('days', function ($user) use ($departmentId, $month, $year) {
                $fullDays = ReservationAllowance::where('user_id', $user->id)
                            ->where('departement_id', $departmentId)
                            ->whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->where('type', 1)
                            ->count();

                $partialDays = ReservationAllowance::where('user_id', $user->id)
                               ->where('departement_id', $departmentId)
                               ->whereYear('date', $year)
                               ->whereMonth('date', $month)
                               ->where('type', 2)
                               ->count();

                return "كلي: $fullDays | جزئي: $partialDays";
            })
            ->addColumn('allowance', function ($user) use ($departmentId, $month, $year) {
                $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                                 ->where('departement_id', $departmentId)
                                 ->whereYear('date', $year)
                                 ->whereMonth('date', $month)
                                 ->where('type', 1)
                                 ->sum('amount');

                $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                    ->where('departement_id', $departmentId)
                                    ->whereYear('date', $year)
                                    ->whereMonth('date', $month)
                                    ->where('type', 2)
                                    ->sum('amount');

                return "كلي: " . number_format($fullAllowance, 2) . " د.ك | جزئي: " . number_format($partialAllowance, 2) . " د.ك";
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function printReport($departmentId, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $department = departements::find($departmentId);

        $employees = User::whereIn('id', function ($query) use ($departmentId, $month, $year) {
                $query->select('user_id')
                      ->from('reservation_allowances')
                      ->where('departement_id', $departmentId)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
            })
            ->with(['grade'])
            ->get();

        $userReservations = $employees->map(function ($user) use ($departmentId, $month, $year) {
            $fullDays = ReservationAllowance::where('user_id', $user->id)
                        ->where('departement_id', $departmentId)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->where('type', 1)
                        ->count();

            $partialDays = ReservationAllowance::where('user_id', $user->id)
                           ->where('departement_id', $departmentId)
                           ->whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->where('type', 2)
                           ->count();

            $fullAllowance = ReservationAllowance::where('user_id', $user->id)
                             ->where('departement_id', $departmentId)
                             ->whereYear('date', $year)
                             ->whereMonth('date', $month)
                             ->where('type', 1)
                             ->sum('amount');

            $partialAllowance = ReservationAllowance::where('user_id', $user->id)
                                ->where('departement_id', $departmentId)
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
}
