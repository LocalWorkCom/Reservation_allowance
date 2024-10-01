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

        if($user->rule_id == 2)
        {
            $reservation_allowances = ReservationAllowance::with('users', 'users.grade', 'departements')->where('date', $to_day)->get();
        }else{
            if($user->department->children->count() > 0){
                $department_id = $user->department->children->pluck('id');
            }else{
                $department_id[] = $user->department_id;
            }
            $reservation_allowances = ReservationAllowance::with('users', 'users.grade', 'departements')->whereIn('departement_id', $department_id)->where('date', $to_day)->get();
        }         
        return view('reservation_allowance.index', compact('reservation_allowances', 'employees', 'to_day', 'to_day_name', 'super_admin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();        
        $employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();
        return view('reservation_allowance.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $messages = [
                'Civil_number.required' => 'رقم الهوية مطلوب ولا يمكن تركه فارغاً.'
            ];
            
            $validatedData = Validator::make($request->all(), [
                'Civil_number' => 'required'
            ], $messages);
    
            if ($validatedData->fails()) {
                return redirect()->back()->withErrors($validatedData)->withInput();
            }
            
            $user = auth()->user();    
            $to_day = Carbon::now()->format('Y-m-d');
            $to_day_name = Carbon::now()->translatedFormat('l');

            $employee = User::findOrFail($request->Civil_number);
            if($employee->grade->count() > 0){
                if($request->type == 1){
                    $grade_value = $employee->grade->value_all;
                }else{
                    $grade_value = $employee->grade->value_part;
                }
            }else{
                return redirect()->back()->with('error','عفوا يجب ان يتم اضافة رتبة  '.$employee->name);
            }
            

            $check_reservation_allowance = ReservationAllowance::where(['user_id' => $employee->id, 'date' => $to_day])->first();
            if($check_reservation_allowance){
                return redirect()->back()->with('error','عفوا تم اضافة بدل لحجز '.$employee->name.' فى هذا اليوم من قبل');
            }
            
            $add_reservation_allowance = new ReservationAllowance();
            $add_reservation_allowance->user_id = $employee->id;
            $add_reservation_allowance->type = $request->type;
            $add_reservation_allowance->amount = $grade_value;
            $add_reservation_allowance->date = $to_day;
            $add_reservation_allowance->day = $to_day_name;
            $add_reservation_allowance->sector_id = $user->department->sector_id;
            $add_reservation_allowance->departement_id = $user->department->id;
            $add_reservation_allowance->created_by = $user->id;
            $add_reservation_allowance->save();
            return redirect()->route('reservation_allowances.index')->with('success', 'تم اضافه بدل حجز بنجاح');
        }catch(\Exception $e){
            return redirect()->back()->with('message', 'An error occurred while creating the group. Please try agai');   
        }
    }

    public function create_all()
    {
        $user = auth()->user();        
        $employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();
        return view('reservation_allowance.create_all', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_all(Request $request)
    {
        try{
            $messages = [
                'Civil_number.required' => 'رقم الهوية مطلوب ولا يمكن تركه فارغاً.'
            ];
            
            $validatedData = Validator::make($request->all(), [
                'Civil_number' => 'required'
            ], $messages);
    
            if ($validatedData->fails()) {
                return redirect()->back()->withErrors($validatedData)->withInput();
            }
            
            $user = auth()->user();    
            $to_day = Carbon::now()->format('Y-m-d');
            $to_day_name = Carbon::now()->translatedFormat('l');

            foreach($request->Civil_number as $Civil_number){
                $employee = User::findOrFail($Civil_number);
                if($request->type == 1){
                    $grade_value = $employee->grade->value_all;
                }else{
                    $grade_value = $employee->grade->value_part;
                }

                $check_reservation_allowance = ReservationAllowance::where(['user_id' => $employee->id, 'date' => $to_day])->first();
                if($check_reservation_allowance){
                    return redirect()->back()->with('error','عفوا تم اضافة بدل لحجز '.$employee->name.' فى هذا اليوم من قبل');
                }
                
                $add_reservation_allowance = new ReservationAllowance();
                $add_reservation_allowance->user_id = $employee->id;
                $add_reservation_allowance->type = $request->type;
                $add_reservation_allowance->amount = $grade_value;
                $add_reservation_allowance->date = $to_day;
                $add_reservation_allowance->day = $to_day_name;
                $add_reservation_allowance->sector_id = $user->department->sector_id;
                $add_reservation_allowance->departement_id = $user->department->id;
                $add_reservation_allowance->created_by = $user->id;
                $add_reservation_allowance->save();
            }

            return redirect()->route('reservation_allowances.index')->with('success', 'تم اضافه بدل حجز بنجاح');
        }catch(\Exception $e){
            return redirect()->back()->with('message', 'An error occurred while creating the group. Please try agai');   
        }
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

        if($user->rule_id == 2)
        {
            $data = ReservationAllowance::with('users', 'users.grade', 'departements')->where('date', $to_day)->get();
        }else{
            if($user->department->children->count() > 0){
                $department_id = $user->department->children->pluck('id');
            }else{
                $department_id[] = $user->department_id;
            }
            $data = ReservationAllowance::with('users', 'users.grade', 'departements')->whereIn('departement_id', $department_id)->where('date', $to_day)->get();
        }   

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
