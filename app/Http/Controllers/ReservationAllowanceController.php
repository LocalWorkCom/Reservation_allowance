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
use Illuminate\Support\Facades\Cache;

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
        //$employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();
        $employees = [];

        if($user->rule_id == 2)
        {
            $sectors = Sector::get();
            $departements = [];
        }else{
            if($user->department_id == null){
                $sectors[] = $user->sectors;
                $departements = departements::where('sector_id', $user->sector);
            }else{
                $sectors[] = $user->sectors;
                $departements = departements::where('id', $user->department_id);
            }
        }

        return view('reservation_allowance.index', compact('sectors', 'departements', 'employees', 'to_day', 'to_day_name', 'super_admin'));
    }

    public function index_data($sector_id, $departement_id, $date)
    {
        $to_day = $date;
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        $super_admin = User::where('department_id', 1)->first();
        $employees = [];
        $get_departements = [];
        $sectors = Sector::get();

        /*if($user->rule_id == 2)
        {
            $sectors = Sector::get();
            $departements = [];
        }else{
            if($departement_id == null){
                $sectors[] = $user->sectors;
                $get_departements = departements::with('children')->where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
            }else{
                $sectors[] = $user->sectors;
                $get_departements = departements::with('children')->where('id', '!=', 1)->where('id', $departement_id)->get();
            }
        }*/

        if($departement_id == null){
            $get_departements = departements::with('children')->where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $get_departements = departements::with('children')->where('id', '!=', 1)->where('id', $departement_id)->get();
        }

        return view('reservation_allowance.index_data', compact('sectors', 'get_departements', 'employees', 'to_day', 'to_day_name', 'super_admin', 'sector_id', 'departement_id'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        $to_day = Carbon::now()->format('Y-m-d');
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        $super_admin = User::where('department_id', 1)->first();
        //$employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();
        $employees = User::where('department_id', $user->department_id)->get();

        if($user->rule_id == 2)
        {
            $sectors = Sector::get();
            $departements = [];
        }else{
            if($user->department_id == null){
                $sectors[] = $user->sectors;
                $departements = departements::where('sector_id', $user->sector);
            }else{
                $sectors[] = $user->sectors;
                $departements = departements::where('id', $user->department_id);
            }
        }

        return view('reservation_allowance.create', compact('sectors', 'departements', 'employees', 'to_day', 'to_day_name', 'super_admin'));
    }

    public function create_employee_new(Request $request)
    {
        $to_day = Carbon::now()->format('Y-m-d');
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        if($user->rule_id == 2)
        {
            $sectors = Sector::get();
        }else{
            if($user->department_id == null){
                $sectors[] = $user->sectors;
            }else{
                $sectors[] = $user->sectors;
            }
        }

        $department_type = $request->department_type;
        $sector_id = 0;
        $departement_id = 0;
        if($request->sector_id){
            $sector_id = $request->sector_id;
        }
        if($request->departement_id){
            $departement_id = $request->departement_id;
        }

        if($user->department_id == 0){
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $user = auth()->user();
            $get_departements = departements::where('id', '!=', 1)->where('id', $user->department_id)->get();
        }

        $data = [];
        $reservation_allowance_type = 0;
        if($sector_id != 0){
            //$data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
            $data = User::query()->where('sector', $sector_id);
            if($departement_id != 0){
                $data = $data->where('department_id', $departement_id);
            }else{
                $data = $data->where('department_id', null);
            }
            $data = $data->get();

            if($departement_id != 0){
                $reservation_allowance_type = departements::where('id', $departement_id)->first()->reservation_allowance_type;
            }else{
                $reservation_allowance_type = Sector::where('id', $sector_id)->first()->reservation_allowance_type;
            }

        }


        $employees = $data;

        return view('reservation_allowance.create_employee_new', compact('department_type', 'sector_id', 'departement_id', 'sectors', 'get_departements', 'employees', 'reservation_allowance_type'));
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

            if($employee->grade_id != null){
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
            $add_reservation_allowance->sector_id = $request->sector_id;
            $add_reservation_allowance->departement_id = $request->departement_id;
            $add_reservation_allowance->grade_id = $employee->grade_id;
            $add_reservation_allowance->created_by = $user->id;
            $add_reservation_allowance->save();
            return redirect()->route('reservation_allowances.index')->with('success', 'تم اضافه بدل حجز بنجاح');
        }catch(\Exception $e){
            return redirect()->back()->with('error', 'An error occurred while creating the group. Please try agai');
        }
    }

    public function create_all()
    {
        $to_day = Carbon::now()->format('Y-m-d');
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        $super_admin = User::where('department_id', 1)->first();
        //$employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();
        $employees = User::where('department_id', $user->department_id)->get();

        if($user->rule_id == 2)
        {
            $sectors = Sector::get();
            $departements = [];
        }else{
            if($user->department_id == null){
                $sectors[] = $user->sectors;
                $departements = departements::where('sector_id', $user->sector);
            }else{
                $sectors[] = $user->sectors;
                $departements = departements::where('id', $user->department_id);
            }
        }

        return view('reservation_allowance.create_all', compact('sectors', 'departements', 'employees', 'to_day', 'to_day_name', 'super_admin'));
    }

    public function create_employee_all(Request $request)
    {
        $to_day = Carbon::now()->format('Y-m-d');
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        if($user->rule_id == 2)
        {
            $sectors = Sector::get();
        }else{
            if($user->department_id == null){
                $sectors[] = $user->sectors;
            }else{
                $sectors[] = $user->sectors;
            }
        }

        $department_type = $request->department_type;
        $sector_id = 0;
        $departement_id = 0;
        if($request->sector_id){
            $sector_id = $request->sector_id;
        }
        if($request->departement_id){
            $departement_id = $request->departement_id;
        }

        if($user->department_id == 0){
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $user = auth()->user();
            $get_departements = departements::where('id', '!=', 1)->where('id', $user->department_id)->get();
        }

        $data = [];
        $reservation_allowance_type = 0;
        if($sector_id != 0){
            //$data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
            $data = User::query()->where('sector', $sector_id);
            if($departement_id != 0){
                $data = $data->where('department_id', $departement_id);
            }else{
                $data = $data->where('department_id', null);
            }
            $data = $data->get();

            if($departement_id != 0){
                $reservation_allowance_type = departements::where('id', $departement_id)->first()->reservation_allowance_type;
            }else{
                $reservation_allowance_type = Sector::where('id', $sector_id)->first()->reservation_allowance_type;
            }

        }

        $employees = $data;

        return view('reservation_allowance.create_employee_all', compact('department_type', 'sector_id', 'departement_id', 'sectors', 'get_departements', 'employees', 'reservation_allowance_type'));
    }

    public function check_store(Request $request)
    {
        $user = auth()->user();
        $to_day = $request->date;
        $to_day_name = Carbon::parse($to_day)->translatedFormat('l');
        $type = $request->type;
        $total_grade_value = 0;

        $get_departements = []; 
        $sector_id = 0;
        $department_id = 0;
        
        $Civil_numbers = str_replace(array("\r","\r\n","\n"),',',$request->Civil_number);
        $Civil_numbers = explode(',,',$Civil_numbers);

        $cache_name = auth()->user()->id."_add_store_all";
        Cache::put($cache_name, $Civil_numbers);
        
        $employee_not_found = array();
        $employee_not_dept = array();
        $employee_new_add = array();
        foreach($Civil_numbers as $Civil_number){//file_number

            // $employee = User::where('Civil_number', $Civil_number)->first();
            $employee = User::where('file_number', $Civil_number)->first();
            if($employee){// check if employee
                if($employee->grade_id != null){ // check if employee has grade
                    if($request->type == 1){
                        $grade_value = $employee->grade->value_all;
                    }else{
                        $grade_value = $employee->grade->value_part;
                    }

                    $check_sector = 1;
                    if($employee->sector != $request->sector_id){
                        $check_sector = 0;
                    }

                    if($employee->department_id != null){
                        if($employee->department_id != $request->departement_id){
                            $check_sector = 0;
                        }
                    }

                    if($employee->department_id == null && $request->departement_id != 0){
                        if($employee->department_id != $request->departement_id){
                            $check_sector = 0;
                        }
                    }

                    if($check_sector == 0){
                        $employee_not_dept[] = $employee;
                        //array_push($employee_not_dept, $employee_not_depts);
                    }

                    if($check_sector == 1){
                        $sector_id = $request->sector_id;
                        $department_id = $request->departement_id;
                        $employee['grade_value'] = $grade_value;
                        $total_grade_value += $grade_value;
                        $employee_new_add[] = $employee;
                        //array_push($employee_new_add, $employee_new_adds);
                    } 
                }
            }else{
                $employee_not_founds = array('Civil_number' => $Civil_number);
                array_push($employee_not_found, $employee_not_founds);
            }
        }

        $sectors = Sector::get();
        if($department_id == 0){
            $get_departements = departements::with('children')->where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $get_departements = departements::with('children')->where('id', '!=', 1)->where('id', $department_id)->get();
        }

        $cache_name = auth()->user()->id."_employee_new_add";
        Cache::put($cache_name, $employee_new_add);
        //return Cache::get($cache_name);

        $current_departement = departements::where('id', $department_id)->first();
        $current_sector = Sector::where('id', $sector_id)->first();

        return view('reservation_allowance.index_check_store', compact('type', 'current_sector','current_departement', 'total_grade_value', 'sectors', 'get_departements', 'to_day', 'employee_not_found', 'employee_not_dept', 'employee_new_add', 'department_id', 'sector_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_all(Request $request)
    {
        try{
            $messages = [
                'sector_id.required' => 'القطاع مطلوب ولا يمكن تركه فارغاً.'
            ];

            $validatedData = Validator::make($request->all(), [
                'sector_id' => 'required'
            ], $messages);

            if ($validatedData->fails()) {
                return redirect()->back()->withErrors($validatedData)->withInput();
            }

            $sector_messages = 'القطاع مطلوب ولا يمكن تركه فارغاً.';
            $employee_new_add_messages = 'لا يوجد موظفين لديهم الاحقية لاضافة بدل حجز لهم.';

            $cache_name = auth()->user()->id."_employee_new_add";
            $employee_new_add = Cache::get($cache_name);

            if($request->sector_id == 0 && $employee_new_add == null){
                return redirect()->back()->withErrors($employee_new_add_messages)->withInput();
            }

            if($request->sector_id == 0 && $employee_new_add != null){
                return redirect()->back()->withErrors($sector_messages)->withInput();
            }

            $user = auth()->user();
            $to_day = $request->date;
            $to_day_name = Carbon::parse($to_day)->translatedFormat('l');

            /*$Civil_numbers = str_replace(array("\r","\r\n","\n"),',',$request->Civil_number);
            $Civil_numbers = explode(',,',$Civil_numbers);*/

            $cache_name = auth()->user()->id."_add_store_all";
            $Civil_numbers = Cache::get($cache_name);


            $employee_amount = 0;
            $reservation_amout = 0;

            foreach($Civil_numbers as $Civil_number){//file_number
                $employee = User::where('file_number', $Civil_number)->first(); 
                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($request->type == 1){
                            $grade_value = $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                        }
                        $employee_amount += $grade_value;
                    }
                }
            }

            $type_departement = 1;
            $reservation_amout = sector::where('id', $request->sector_id)->first()->reservation_allowance_amount;
            if($request->departement_id != 0){
                $type_departement = 2;
                $reservation_amout = departements::where('id', $request->departement_id)->first()->reservation_allowance_amount;
            }


            $first_day = date('Y-m-01');
            $last_day = date('Y-m-t');
            $get_all_employee_amount = ReservationAllowance::Query();
            if($request->departement_id != 0){
                $get_all_employee_amount = $get_all_employee_amount->where('departement_id', $request->departement_id);
            }
            if($request->sector_id != 0){
                $get_all_employee_amount = $get_all_employee_amount->where('sector_id', $request->sector_id);
            }

            $get_all_employee_amount = $get_all_employee_amount->whereBetween('date',[$first_day, $last_day])->sum('amount');

            if($reservation_amout > 0){
                $reservation_amout = $reservation_amout - $get_all_employee_amount;
                if($reservation_amout != 0 && $reservation_amout <= $employee_amount){
                    return redirect()->route('reservation_allowances.create.all')->with('error','عفوا لقد تجاوزت ملبغ بدل الحجز');
                }
            }

    
            $employee_not_add = array();
            foreach($Civil_numbers as $Civil_number){//file_number

               // $employee = User::where('Civil_number', $Civil_number)->first();
               $employee = User::where('file_number', $Civil_number)->first();
                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($request->type == 1){
                            $grade_value = $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                        }

                        $check_sector = 1;
                        if($employee->sector != $request->sector_id){
                            $check_sector = 0;
                        }

                        if($employee->department_id != 0){
                            if($employee->department_id != $request->departement_id){
                                $check_sector = 0;
                            }
                        }

                        $check_reservation_allowance = ReservationAllowance::where(['user_id' => $employee->id, 'date' => $to_day])->first();
                        if(!$check_reservation_allowance){
                            if($check_sector == 1){
                                //return redirect()->back()->with('error','عفوا تم اضافة بدل لحجز '.$employee->name.' فى هذا اليوم من قبل');
                                $add_reservation_allowance = new ReservationAllowance();
                                $add_reservation_allowance->user_id = $employee->id;
                                $add_reservation_allowance->type = $request->type;
                                $add_reservation_allowance->amount = $grade_value;
                                $add_reservation_allowance->date = $to_day;
                                $add_reservation_allowance->day = $to_day_name;
                                $add_reservation_allowance->sector_id = $employee->sector;
                                $add_reservation_allowance->departement_id = $employee->department_id;
                                $add_reservation_allowance->grade_id = $employee->grade_id;
                                $add_reservation_allowance->created_by = $user->id;
                                $add_reservation_allowance->save();
                            }
                        }
                    }
                }
            }

            return redirect()->route('reservation_allowances.index_data',[$request->sector_id, $request->departement_id, $to_day])->with('success', 'تم اضافه بدل حجز بنجاح');
        }catch(\Exception $e){
            return redirect()->back()->with('error', 'An error occurred while creating the group. Please try agai');
        }
    }

    public function getAll(Request $request)
    {
        $user = auth()->user();
        $to_day = Carbon::now()->format('Y-m-d');
        $data = [];

        if($request->has('date')){
            $date = $request->input('date');
        }else{
            $date = Carbon::now()->format('Y-m-d');
        }
        if($request->has('sector_id')){
            $sector_id = $request->input('sector_id');
        }else{
            $sector_id = 0;
        }
        if($request->has('departement_id')){
            $departement_id = $request->input('departement_id');
        }else{
            $departement_id = 0;
        }

        $data = ReservationAllowance::Query()->with('users', 'users.grade', 'departements')->where('date', $date);
        if($sector_id != 0){
            $data = $data->where('sector_id', $sector_id);
        }
        if($departement_id != 0){
            $data = $data->where('departement_id', $departement_id);
        }
        $data = $data->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '<button class="btn  btn-sm" style="background-color: #259240;"><i class="fa fa-edit"></i></button>';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->users->name;  // Display the count of iotelegrams
            })
            ->addColumn('employee_grade', function ($row) {
                  // Display the count of iotelegrams
                if($row->users->grade_id != null){return $row->users->grade->name;}else{return "لا يوجد رتبة";}

            })
            ->addColumn('employee_file_num', function ($row) {
                if($row->users->file_number == null){return "لا يوجد رقم ملف";}else{return $row->users->file_number;}
            })
            /*->addColumn('type', function ($row) {
                return $row->type;
            })*/
            ->addColumn('employee_allowance_type_btn', function ($row) {
                if($row->type == '1'){
                    $btn = '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" class="form-control" checked disabled></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio" class="form-control" disabled></div></div>';
                }else{
                    $btn = '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" class="form-control" disabled></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio"class="form-control" checked disabled></div></div>';
                }
                return $btn;
            })
            ->addColumn('employee_allowance_amount', function ($row) {
                return $row->amount.' د.ك ';  // Display the count of iotelegrams
            })

            ->rawColumns(['employee_allowance_type_btn'])
            //->rawColumns(['action'])
            ->make(true);
    }

    public function getAllWithMonth(Request $request)
    {
        $user = auth()->user();
        $to_day = Carbon::now()->format('Y-m-d');
        $data = [];

        if($request->has('year')){
            $year = $request->input('year');
        }else{
            $year = Carbon::now()->format('Y');
        }
        if($request->has('month')){
            $month = $request->input('month');
        }else{
            $month = Carbon::now()->format('m');
        }
        if($request->has('sector_id')){
            $sector_id = $request->input('sector_id');
        }else{
            $sector_id = 0;
        }
        if($request->has('departement_id')){
            $departement_id = $request->input('departement_id');
        }else{
            $departement_id = 0;
        }

        $data = ReservationAllowance::Query()->with('users', 'users.grade', 'departements')->whereMonth('date', $month)->whereYear('date', $year);
        if($sector_id != 0){
            $data = $data->where('sector_id', $sector_id);
        }else{
            if($user->rule_id != 2){
                $data = $data->where('sector_id', $user->sector);
            }
        }

        if($departement_id != 0){
            $data = $data->where('departement_id', $departement_id);
        }else{
            if($user->rule_id == 3){
                $data = $data->where('departement_id', $user->department_id);
            }
        }
        $data = $data->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '<button class="btn  btn-sm" style="background-color: #259240;"><i class="fa fa-edit"></i></button>';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->users->name;  // Display the count of iotelegrams
            })
            ->addColumn('employee_grade', function ($row) {
                  // Display the count of iotelegrams
                if($row->users->grade_id != null){return $row->users->grade->name;}else{return "لا يوجد رتبة";}

            })
            ->addColumn('employee_file_num', function ($row) {
                if($row->users->file_number == null){return "لا يوجد رقم ملف";}else{return $row->users->file_number;}
            })
            /*->addColumn('type', function ($row) {
                return $row->type;
            })*/
            ->addColumn('employee_allowance_type_btn', function ($row) {
                if($row->type == '1'){
                    $btn = '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" class="form-control" checked disabled></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio" class="form-control" disabled></div></div>';
                }else{
                    $btn = '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" class="form-control" disabled></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio"class="form-control" checked disabled></div></div>';
                }
                return $btn;
            })
            ->addColumn('employee_allowance_amount', function ($row) {
                return $row->amount.' د.ك ';  // Display the count of iotelegrams
            })

            ->rawColumns(['employee_allowance_type_btn'])
            //->rawColumns(['action'])
            ->make(true);
    }
    
    public function get_departement($sector_id, $type)
    {
       // dd($type);
        if($type == 1){
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $user = auth()->user();
            $get_departements = departements::where('id', '!=', 1)->where('id', $user->department_id)->get();
        }
        $departement_id = 0;

        return view('reservation_allowance.get_departements', compact('get_departements', 'departement_id'));
    }

    public function get_crate_all_form($sector_id, $department_id)
    {
        $reservation_allowance_type = 0;
        if($sector_id != 0){
            if($department_id != 0){
                $reservation_allowance_type = departements::where('id', $department_id)->first()->reservation_allowance_type;
            }else{
                $reservation_allowance_type = Sector::where('id', $sector_id)->first()->reservation_allowance_type;
            }
        }
        $today = date('Y-m-d');
        return view('reservation_allowance.create_all_form', compact('sector_id', 'department_id', 'reservation_allowance_type', 'today'));
    }

    public function get_check_sector_department($sector_id, $department_id, $civil_numbers)
    {
        //check employee in same sector or department
        $civil_numbers = explode(',,',$civil_numbers);

        $check_sector = 0;
        $check_department = 0;
        foreach($civil_numbers as $civil_number){//file_number
           // $employee = User::where('Civil_number', $civil_number)->first();
           $employee = User::where('file_number', $civil_number)->first();

            if($employee){// check if employee
                if($employee->sector != $sector_id){
                    $check_sector++;
                }
                if($employee->department_id != $department_id){
                    $check_department++;
                }
            }
        }

        return view('reservation_allowance.check_sector_department', compact('check_sector', 'check_department'));
    }

    public function search_employee_new(Request $request)
    {
        /*$messages = [
            'sector_id.required' => 'اختيار القطاع مطلوب ولا يمكن تركه فارغاً.'
        ];

        $validatedData = Validator::make($request->all(), [
            'sector_id' => 'required'
        ], $messages);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }*/

        $today = Carbon::now()->format('Y-m-d');
        if($request->has('date')){
            $today = $request->date;
        }

        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        if($user->rule_id == 2)
        {
            $sectors = Sector::get();
        }else{
            if($user->department_id == null){
                $sectors[] = $user->sectors;
            }else{
                $sectors[] = $user->sectors;
            }
        }

        $department_type = $request->department_type;
        $sector_id = 0;
        $departement_id = 0;
        $get_departements = [];
        if($request->sector_id){
            $sector_id = $request->sector_id;
        }

        if($request->departement_id){
            $departement_id = $request->departement_id;
            if($user->department_id == null){
                $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
            }else{
                //$user = auth()->user();
                $get_departements = departements::where('id', '!=', 1)->where('id', $user->department_id)->get();
            }
        }else{
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->get();  
        }


        $reservation_allowance_type = 0;
        if($sector_id != 0){
            if($departement_id != 0){
                $reservation_allowance_type = departements::where('id', $departement_id)->first()->reservation_allowance_type;
            }else{
                $reservation_allowance_type = Sector::where('id', $sector_id)->first()->reservation_allowance_type;
            }
        }



        $data = [];

        if($sector_id != 0){
            //$data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
            $data = User::query()->where('sector', $sector_id);
            $get_employee_reservation = ReservationAllowance::Query()->where('date', $today)->where('sector_id', $sector_id);
            if($departement_id != 0){
                $data = $data->where('department_id', $departement_id);
                $get_employee_reservation = $get_employee_reservation->where('departement_id', $departement_id);
            }else{
                $data = $data->where('department_id', null);
                $get_employee_reservation = $get_employee_reservation->where('departement_id', null);
            }
            $get_employee_reservation = $get_employee_reservation->pluck('user_id');
            $data = $data->whereNotIn('id', $get_employee_reservation);
            $data = $data->get();
        }

        $employees = $data;

        return view('reservation_allowance.search_employee_new', compact('today' ,'department_type', 'reservation_allowance_type', 'sector_id', 'departement_id', 'sectors', 'get_departements', 'employees'));
    }

    public function search_employee(Request $request)
    {
        $to_day = Carbon::now()->format('Y-m-d');
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        if($user->rule_id == 2)
        {
            $sectors = Sector::get();
        }else{
            if($user->department_id == null){
                $sectors[] = $user->sectors;
            }else{
                $sectors[] = $user->sectors;
            }
        }

        $department_type = $request->department_type;
        $sector_id = 0;
        $departement_id = 0;
        if($request->sector_id){
            $sector_id = $request->sector_id;
        }
        if($request->departement_id){
            $departement_id = $request->departement_id;
        }

        if($user->department_id == null){
            $get_departements = departements::where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $user = auth()->user();
            $get_departements = departements::where('id', $user->department_id)->get();
        }

        return view('reservation_allowance.search_employee', compact('department_type', 'sector_id', 'departement_id', 'sectors', 'get_departements'));
    }

    public function get_search_employee($sector_id,$departement_id)
    {
        $user = auth()->user();
        $to_day = Carbon::now()->format('Y-m-d');

        if($sector_id != 0){
            //$data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
            $data = User::query()->where('sector', $sector_id);
            if($departement_id != 0){
                $data = $data->where('department_id', $departement_id);
            }
        }

        $data = $data->get();


        /*if($user->rule_id == 2)
        {
            $data = User::where('date', $to_day)->get();
        }else{
            if($user->department->children->count() > 0){
                $department_id = $user->department->children->pluck('id');
            }else{
                $department_id[] = $user->department_id;
            }
            $data = ReservationAllowance::with('users', 'users.grade', 'departements')->whereIn('departement_id', $department_id)->where('date', $to_day)->get();
        }*/

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '<button class="btn  btn-sm" style="background-color: #259240;"><i class="fa fa-edit"></i></button>';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->name;  // Display the count of iotelegrams
            })
            ->addColumn('employee_grade', function ($row) {
                  // Display the count of iotelegrams
                if($row->grade_id != null){return $row->grade->name;}else{return "لا يوجد رتبة";}

            })
            ->addColumn('employee_file_num', function ($row) {
                if($row->file_number == null){return "لا يوجد رقم ملف";}else{return $row->file_number;}
            })

            ->addColumn('employee_allowance_type_btn', function ($row) {
                return $btn = '<div class="d-flex" style="justify-content: space-around !important" id="'.$row->id.'"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" name="allowance[]['.$row->id.']" id="allowance[1]['.$row->id.']" value="1" class="form-control c-radio"></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio" name="allowance[]['.$row->id.']" id="allowance[2]['.$row->id.']" value="2" class="form-control c-radio"></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  لا يوجد</label><input type="radio" name="allowance[]['.$row->id.']" id="allowance[0]['.$row->id.']" value="0" checked class="form-control c-radio"></div></div>';
            })
            ->addColumn('employee_allowance_amount', function ($row) {
                return $row->amount.' د.ك ';  // Display the count of iotelegrams
            })

            ->rawColumns(['employee_allowance_type_btn'])
            //->rawColumns(['action'])
            ->make(true);
    }

    public function add_reservation_allowances_employess($type, $id)
    {
        $get_employees = Cache::get(auth()->user()->id);
        if($get_employees != null){
            foreach($get_employees as $k_get_employee=>$get_employee){
                if(in_array($id, $get_employee)){
                    unset($get_employees[$k_get_employee]);
                    $get_employees = array_values($get_employees);
                    Cache::put(auth()->user()->id,$get_employees);
                }
            }
        }
        
        $get_employees[] = ['id'=>$id, 'type'=>$type]; 
        Cache::put(auth()->user()->id, $get_employees);
        return Cache::get(auth()->user()->id);
    }

    public function view_reservation_allowances_employess()
    {
        //Cache::forget(auth()->user()->id);
        return Cache::get(auth()->user()->id);
    }

    public function confirm_reservation_allowances($date,$sector_id=0,$departement_id=0)
    {
        $sector = $sector_id;
        $departement = $departement_id;
        $datey = $date;
        if(Cache::has(auth()->user()->id)){

            $user = auth()->user();
            $to_day = $date;
            //$to_day = Carbon::now()->format('Y-m-d');
            $to_day_name = Carbon::parse($date)->translatedFormat('l');
            $get_employees = Cache::get(auth()->user()->id);
            $employee_amount = 0;
            $reservation_amout = 0;
            // $sector_id = 0;
            // $departement_id = 0;
            
            foreach($get_employees as $get_employee){
                $employee = User::where('id', $get_employee['id'])->first();

                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($get_employee['type'] == 1){
                            $grade_value = $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                        }

                        $employee_amount += $grade_value;

                        /*$type_departement = 1;
                        $reservation_amout = departements::where('id', $employee->department_id)->first()->reservation_allowance_amount;
                        if($employee->department_id == null){
                            $type_departement = 2;
                            $reservation_amout = departements::where('id', $employee->sector)->first()->reservation_allowance_amount;
                        }*/

                        // $type_departement = 1;
                        //     $reservation_amout = sector::where('id', $employee->sector)->first()->reservation_allowance_amount;
                        // if($employee->department_id != 0){
                        //     $type_departement = 2;
                        //     $reservation_amout = departements::where('id', $employee->department_id)->first()->reservation_allowance_amount;
                        // }
                        

                    }
                }
            }


            $type_departement = 1;
            $reservation_amout = sector::where('id', $sector_id)->first()->reservation_allowance_amount;
            if($departement_id != 0){
                $type_departement = 2;
                $reservation_amout = departements::where('id', $departement_id)->first()->reservation_allowance_amount;
            }


            $first_day = date('Y-m-01');
            $last_day = date('Y-m-t');
            
            $get_all_employee_amount = ReservationAllowance::Query();
            if($departement_id != 0){
                $get_all_employee_amount = $get_all_employee_amount->where('departement_id', $departement_id);
            }
            if($sector_id != 0){
                $get_all_employee_amount = $get_all_employee_amount->where('sector_id', $sector_id);
            }
            $get_all_employee_amount = $get_all_employee_amount->whereBetween('date',[$first_day, $last_day])->sum('amount');

            if($reservation_amout > 0){
                $reservation_amout = $reservation_amout - $get_all_employee_amount;           
                if($reservation_amout <= $employee_amount){
                    return redirect()->back()->with('error','عفوا لقد تجاوزت ملبغ بدل الحجز');
                }
            }
            
            //add ReservationAllowance
            foreach($get_employees as $get_employee){

                $employee = User::where('id', $get_employee['id'])->first();
                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($get_employee['type'] == 1){
                            $grade_value = $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                        }

                        // $type_departement = 1;
                        // if($employee->department_id == null){
                        //     $type_departement = 2;
                        // }


                        if($get_employee['type'] != 0){
                            $sector_id = $employee->sector;
                            $departement_id = $employee->department_id;
                            $check_reservation_allowance = ReservationAllowance::updateOrCreate(
                                [
                                    'user_id' => $employee->id,
                                    'date' => $to_day
                                ],
                                [
                                    'type' => $get_employee['type'],
                                    'amount' => $grade_value,
                                    'day' => $to_day_name,
                                    'sector_id' => $employee->sector,
                                    'departement_id' => $employee->department_id,
                                    'grade_id' => $employee->grade_id,
                                    'type_departement' => $type_departement,
                                    'created_by' => $user->id
                                ]
                            );
                        }

                        if($get_employee['type'] == 0){
                            $check_reservation_allowance = ReservationAllowance::where(['user_id' => $employee->id, 'date' => $to_day])->first();
                            if($check_reservation_allowance){
                                $check_reservation_allowance->delete();
                            }
                        }

                    }
                }
            }
            Cache::forget(auth()->user()->id);
        }
        //return redirect()->route('reservation_allowances.index')->with('success', 'تم اضافه بدل حجز بنجاح');
        return redirect()->route('reservation_allowances.index_data',[$sector, $departement, $datey])->with('success', 'تم اضافه بدل حجز بنجاح');
    }
}
