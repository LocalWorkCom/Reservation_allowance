<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use App\Models\ReservationAllowance;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class SubDepartmentStatsController extends Controller
{
    public function index($departmentId, Request $request)
    {
        $department = departements::find($departmentId);

        return view('reservation_subdeparts.index', [
            'departmentId' => $departmentId,
            'departmentName' => $department ? $department->name : 'Unknown Department',
            'month' => $request->query('month'),
            'year' => $request->query('year'),
        ]);
    }

    public function getAll($departmentId, Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $query = departements::where('parent_id', $departmentId);

        return DataTables::of($query)
            ->addColumn('sub_department_name', fn($row) => $row->name)
            ->addColumn('reservation_allowance_budget', function ($row) use ($month, $year) {
                $amount = ReservationAllowance::where('departement_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');

                return number_format($amount, 2) . " د.ك";
            })
            ->addColumn('registered_by', function ($row) use ($month, $year) {
                $sum = ReservationAllowance::where('departement_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');

                return number_format($sum, 2) . " د.ك";
            })
            ->addColumn('remaining_amount', function ($row) use ($month, $year) {
                $registeredAmount = ReservationAllowance::where('departement_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');

                $remainingAmount = $row->reservation_allowance_amount - $registeredAmount;

                return number_format($remainingAmount, 2) . " د.ك";
            })
            ->addColumn('employees_count', function ($row) {
                return User::where('department_id', $row->id)->count();
            })
            ->addColumn('received_allowance_count', function ($row) use ($month, $year) {
                return ReservationAllowance::where('departement_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->distinct('user_id')
                    ->count('user_id');
            })
            ->addColumn('did_not_receive_allowance_count', function ($row) use ($month, $year) {
                $employeesCount = User::where('department_id', $row->id)->count();
                $receivedAllowanceCount = ReservationAllowance::where('departement_id', $row->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->distinct('user_id')
                    ->count('user_id');

                return $employeesCount - $receivedAllowanceCount;
            })
            ->make(true);
    }
}
