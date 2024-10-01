<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sector;
use App\Models\departements;
use App\Models\ReservationAllowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ReservationAllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {

        $to_day = Carbon::now()->format('Y-m-d');
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();        
        $super_admin = User::where('department_id', 1)->first();
        $employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();
        $reservation_allowances = ReservationAllowance::with('users', 'departements')->get();
        return view('reservation_allowance.index', compact('reservation_allowances', 'employees', 'to_day', 'to_day_name', 'super_admin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    public function getAll()
    {
        $user = auth()->user();
        $to_day = Carbon::now()->format('Y-m-d');
        $data = ReservationAllowance::with('users', 'users.grade', 'departements')->where('departement_id', $user->department_id)->where('date', $to_day)->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '<button class="btn  btn-sm" style="background-color: #259240;"><i class="fa fa-edit"></i></button>';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->users->name;  // Display the count of iotelegrams
            })
            ->addColumn('employee_grade', function ($row) {
                return $row->users->grade->name;  // Display the count of iotelegrams
            })
            ->addColumn('employee_file_num', function ($row) {
                return $row->users->file_number;  // Display the count of iotelegrams
            })
            ->addColumn('employee_allowance_type_btn', function ($row) {
                if($row->type == 1){
                    return '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" id="value_all" name="type" class="form-control" checked value="1" disabled></div><span>|</span>
                     <div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio" id="value_all" name="type" class="form-control" value="2" disabled></div></div>';
                }else{
                    return '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" id="value_all" name="type" class="form-control" value="1" disabled></div><span>|</span>
                     <div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio" id="value_all" name="type" class="form-control" value="2" checked disabled></div></div>';
                }
            })
            ->addColumn('employee_allowance_amount', function ($row) {
                return $row->amount;  // Display the count of iotelegrams
            })

            ->rawColumns(['employee_allowance_type_btn'])
            //->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
