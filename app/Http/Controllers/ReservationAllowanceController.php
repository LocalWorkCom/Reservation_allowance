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
use TCPDF;

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

        // $current_departement = departements::where('uuid', $departement_id)->first();
        // $current_sector = Sector::where('uuid', $sector_id)->first();
        $current_departement = departements::where('uuid', $departement_id)->first();
        if($current_departement){
            $department_id = $current_departement->id;
        }else{
            $department_id = 0;
        }
        $current_sector = Sector::where('uuid', $sector_id)->first();
        if($current_sector){
            $sector_id = $current_sector->id;
        }else{
            $sector_id = 0;
        }


        if($departement_id == null){
            $get_departements = departements::with('children')->where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $get_departements = departements::with('children')->where('id', '!=', 1)->where('id', $department_id)->get();
        }


        return view('reservation_allowance.index_data', compact('sectors', 'get_departements', 'current_departement', 'current_sector', 'employees', 'to_day', 'to_day_name', 'super_admin', 'sector_id', 'departement_id'));
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
        $sectorId = $request->sector_id;
        $departmentId = $request->departement_id;
        
        $Civil_numbers = str_replace(array("\r","\r\n","\n"),',',$request->Civil_number);
        $Civil_numbers = explode(',,',$Civil_numbers);

        $cache_name = auth()->user()->id."_add_store_all";
        Cache::put($cache_name, $Civil_numbers);
        
        $current_departement = departements::where('uuid', $request->departement_id)->first();
        if($current_departement){
            $department_id = $current_departement->id;
        }
        $current_sector = Sector::where('uuid', $request->sector_id)->first();
        if($current_sector){
            $sector_id = $current_sector->id;
        }

        $employee_not_found = array();
        $employee_not_dept = array();
        $employee_new_add = array();
        $employee_existing = array();
        $employee_new_add_id = array();

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
                    if($employee->sector != $sector_id){
                        $check_sector = 0;
                    }

                    if($employee->department_id != null){
                        if($employee->department_id != $department_id){
                            $check_sector = 0;
                        }
                    }

                    if($employee->department_id == null && $request->departement_id != 0){
                        if($employee->department_id != $department_id){
                            $check_sector = 0;
                        }
                    }

                    $get_reservations = ReservationAllowance::where(['date'=>$to_day, 'user_id'=>$employee->id])->first();
                    if($get_reservations)
                    {
                        $check_sector = 2;
                        $employee_existing[] = $employee;
                    }

                    if($check_sector == 0){
                        $employee_not_dept[] = $employee;
                        //array_push($employee_not_dept, $employee_not_depts);
                    }

                    if($check_sector == 1){
                        $sector_id = $sector_id;
                        $department_id = $department_id;
                        $employee['grade_value'] = $grade_value;
                        $total_grade_value += $grade_value;
                        $employee_new_add[] = $employee;
                        $employee_new_add_id[] = $employee->uuid;                        
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
        //Cache::put($cache_name, $employee_new_add);
        Cache::put($cache_name, $employee_new_add_id);
        //return Cache::get($cache_name);


        return view('reservation_allowance.index_check_store', compact('type', 'current_sector','current_departement', 'total_grade_value', 'sectors', 'get_departements', 'to_day', 'employee_not_found', 'employee_not_dept', 'employee_new_add', 'employee_existing', 'departmentId', 'sectorId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_all(Request $request)
    {
        //try{
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
            $department_id = null;

            if($request->departement_id != 0){
                $current_departement = departements::where('uuid', $request->departement_id)->first();
                if($current_departement){
                    $department_id = $current_departement->id;
                }
            }
            
            $current_sector = Sector::where('uuid', $request->sector_id)->first();
            if($current_sector){
                $sector_id = $current_sector->id;
            }
            $employee_amount = 0;
            $reservation_amout = 0;    

            /*$Civil_numbers = str_replace(array("\r","\r\n","\n"),',',$request->Civil_number);
            $Civil_numbers = explode(',,',$Civil_numbers);*/

            /*$cache_name = auth()->user()->id."_add_store_all";
            $Civil_numbers = Cache::get($cache_name);

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
            $reservation_amout = sector::where('uuid', $request->sector_id)->first()->reservation_allowance_amount;
            if($request->departement_id != 0){
                $type_departement = 2;
                $reservation_amout = departements::where('uuid', $request->departement_id)->first()->reservation_allowance_amount;
            }


            $first_day = date('Y-m-01');
            $last_day = date('Y-m-t');
            $get_all_employee_amount = ReservationAllowance::Query();
            if($request->departement_id != 0){
                $get_all_employee_amount = $get_all_employee_amount->where('departement_id', $department_id);
            }
            if($request->sector_id != 0){
                $get_all_employee_amount = $get_all_employee_amount->where('sector_id', $sector_id);
            }

            $get_all_employee_amount = $get_all_employee_amount->whereBetween('date',[$first_day, $last_day])->sum('amount');

            if($reservation_amout > 0){
                $reservation_amout = $reservation_amout - $get_all_employee_amount;
                if($reservation_amout != 0 && $reservation_amout <= $employee_amount){
                    return redirect()->route('reservation_allowances.create.all')->with('error','عفوا لقد تجاوزت ملبغ بدل الحجز');
                }
            }*/
    
            $employee_not_add = array();
            $sector_mandate = 0;
            $department_mandate = 0;
            foreach($employee_new_add as $employee_add){//file_number

               // $employee = User::where('Civil_number', $Civil_number)->first();
               $employee = User::where('uuid', $employee_add)->first();
                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($request->type == 1){
                            $grade_value = $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                        }
                        
                        $check_reservation_allowance = ReservationAllowance::where(['user_id' => $employee->id, 'date' => $to_day])->first();
                        if(!$check_reservation_allowance){
                            //return redirect()->back()->with('error','عفوا تم اضافة بدل لحجز '.$employee->name.' فى هذا اليوم من قبل');
                            $add_reservation_allowance = new ReservationAllowance();
                            $add_reservation_allowance->user_id = $employee->id;
                            $add_reservation_allowance->type = $request->type;
                            $add_reservation_allowance->amount = $grade_value;
                            $add_reservation_allowance->date = $to_day;
                            $add_reservation_allowance->day = $to_day_name;

                            $add_reservation_allowance->sector_id  = $sector_id;
                            $add_reservation_allowance->departement_id = $department_id;

                            //$add_reservation_allowance->department_mandate = ($department_id != null ? $employee->department_id : $department_id);
                            $add_reservation_allowance->grade_id = $employee->grade_id;
                            $add_reservation_allowance->created_by = $user->id;

                            if($employee->sector != $sector_id){
                                $sector_mandate = $sector_id;
                                $add_reservation_allowance->sector_mandate = $employee->sector;
                            }
    
                            if($department_id != 0){
                                if($employee->department_id != $department_id){
                                    $department_mandate = $department_id;
                                    $add_reservation_allowance->department_mandate = $employee->department_id;
                                }
                            }else{
                                if($employee->department_id != null){
                                    $sector_mandate = $sector_id;
                                    $add_reservation_allowance->sector_mandate = $employee->sector;
                                    $add_reservation_allowance->department_mandate = $employee->department_id;
                                }
                            }

                            if($sector_mandate != 0 || $department_mandate != 0){
                                $add_reservation_allowance->mandate = 1;
                            }
    
                            $add_reservation_allowance->save();

                            $sector_mandate = 0;
                            $department_mandate = 0;
                        }
                    }
                }
            }

            return redirect()->route('reservation_allowances.index_data',[$request->sector_id, $request->departement_id, $to_day])->with('success', 'تم اضافه بدل حجز بنجاح');
        // }catch(\Exception $e){
        //     return redirect()->back()->with('error', 'An error occurred while creating the group. Please try agai');
        // }
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

        //check sector
        if($request->has('sector_id')){
            $sectorId = $request->input('sector_id');
            $sectorDetails = Sector::where('uuid', $sectorId)->first();
            if($sectorDetails){
                $sector_id = $sectorDetails->id;
            }else{
                $sector_id = 0;
            }
        }else{
            $sector_id = 0;
        }

        //check department
        if($request->has('departement_id')){
            $departementId = $request->input('departement_id');
            $departementDetails = departements::where('uuid', $departementId)->first();
            if($departementDetails){
                $departement_id = $departementDetails->id;
            }else{
                $departement_id = 0;
            }
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
            ->addColumn('employee_allowance_all_btn', function ($row) {
                if($row->type == '1'){
                    $btn = '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" class="form-control" checked disabled></div><span>';
                }else{
                    $btn = "";
                }
                return $btn;
            })
            ->addColumn('employee_allowance_part_btn', function ($row) {
                if($row->type == '2'){
                    $btn = '<div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio"class="form-control" checked disabled></div></div>';
                }else{
                    $btn = "";
                }
                return $btn;
            })
            ->addColumn('employee_allowance_amount', function ($row) {
                return $row->amount.' د.ك ';  // Display the count of iotelegrams
            })
            ->addColumn('notes', function ($row) {
                if($row->mandate == 1){
                    return "منتدب";
                }
            })

            ->rawColumns(['employee_allowance_all_btn', 'employee_allowance_part_btn'])
            //->rawColumns(['action'])
            ->make(true);
    }

    public function getAllWithMonth(Request $request)
    {
        $user_gest = auth()->user();
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

        //check sector
        if($request->has('sector_id')){
            $sectorId = $request->input('sector_id');
            $sectorDetails = Sector::where('uuid', $sectorId)->first();
            if($sectorDetails){
                $sector_id = $sectorDetails->id;
            }else{
                $sector_id = 0;
            }
        }else{
            $sector_id = 0;
        }

        //check department
        if($request->has('departement_id')){
            $departementId = $request->input('departement_id');
            $departementDetails = departements::where('uuid', $departementId)->first();
            if($departementDetails){
                $departement_id = $departementDetails->id;
            }else{
                $departement_id = 0;
            }
        }else{
            $departement_id = 0;
        }

        if($sector_id == 0){
            if($user_gest->rule_id != 2){
                $sector_id = $user_gest->sector;
            }
        }

        if($departement_id == 0){
            if($user_gest->rule_id == 3){
                $departement_id = $user_gest->department_id;
            }
        }

        $data = User::whereIn('id', function ($query) use ($user_gest, $sector_id, $departement_id, $month, $year) {
            $query->select('user_id')
                  ->from('reservation_allowances');
                  if($sector_id != 0){
                    $query->where('sector_id', $sector_id);
                  }else{
                        if($user_gest->rule_id != 2){
                            $query->where('sector_id', $sector_id);
                        }
                  }
                  if($departement_id != 0){
                    $query->where('departement_id', $departement_id);
                  }else{
                        if($user_gest->rule_id == 3){
                            $query->where('departement_id', $departement_id);
                        }
                  }
            $query->whereYear('date', $year)
                  ->whereMonth('date', $month);
        })
        ->with(['department'])
        ->with(['grade' => function ($q) {
            $q->orderBy('type', 'desc');
            //$q->orderBy('type', 'asc');
        }])
        ->get();


        $total_amount_reservation = ReservationAllowance::whereYear('date', $year)
                        ->whereMonth('date', $month);
                    if($sector_id != 0){
                        $total_amount_reservation->where('sector_id', $sector_id);
                    }else{
                        if($user_gest->rule_id != 2){
                            $total_amount_reservation->where('sector_id', $sector_id);
                        }
                    }
                    if($departement_id != 0){
                        $total_amount_reservation->where('departement_id', $departement_id);
                    }else{
                        if($user_gest->rule_id == 3){
                            $total_amount_reservation->where('departement_id', $departement_id);
                        }
                    }
        $total_amount = $total_amount_reservation->sum("amount");

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
            ->addColumn('allowance_all_count_but', function ($user) use ($user_gest ,$sector_id, $departement_id, $sectorId, $departementId, $month, $year) {
                $allowance_all_count = ReservationAllowance::where('user_id', $user->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', '1');
                if($sector_id != 0){
                    $allowance_all_count->where('sector_id', $sector_id);
                }else{
                    if($user_gest->rule_id != 2){
                        $allowance_all_count->where('sector_id', $sector_id);
                    }
                }
                if($departement_id != 0){
                    $allowance_all_count->where('departement_id', $departement_id);
                }else{
                    if($user_gest->rule_id == 3){
                        $allowance_all_count->where('departement_id', $departement_id);
                    }
                }
                $allowance_all_count->get();

                //return "بدل حجز كلى "."( ".$allowance_all_count." )";
                return "<a href=".route('reservation_allowances.details',[$user->uuid, $sectorId, $departementId, $month, $year,1]).">".$allowance_all_count->count()."</a>";
            })

            ->addColumn('allowance_part_count_but', function ($user) use ($user_gest ,$sector_id, $departement_id, $sectorId, $departementId, $month, $year) {
                $allowance_part_count = ReservationAllowance::where('user_id', $user->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', '2');
                if($sector_id != 0){
                    $allowance_part_count->where('sector_id', $sector_id);
                }else{
                    if($user_gest->rule_id != 2){
                        $allowance_part_count->where('sector_id', $sector_id);
                    }
                }
                if($departement_id != 0){
                    $allowance_part_count->where('departement_id', $departement_id);
                }else{
                    if($user_gest->rule_id == 3){
                        $allowance_part_count->where('departement_id', $departement_id);
                    }
                }
                $allowance_part_count->get();

                //return "بدل حجز جزئى "."( ".$allowance_part_count." )";
                //return $allowance_part_count->count();
                return "<a href=".route('reservation_allowances.details',[$user->uuid, $sectorId, $departementId, $month, $year,2]).">".$allowance_part_count->count()."</a>";
            })

            ->addColumn('allowance_sum_but', function ($user) use ($user_gest ,$sector_id, $departement_id, $month, $year) {
                $allowance_sum = ReservationAllowance::where('user_id', $user->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);
                if($sector_id != 0){
                    $allowance_sum->where('sector_id', $sector_id);
                }else{
                    if($user_gest->rule_id != 2){
                        $allowance_sum->where('sector_id', $sector_id);
                    }
                }
                if($departement_id != 0){
                    $allowance_sum->where('departement_id', $departement_id);
                }else{
                    if($user_gest->rule_id == 3){
                        $allowance_sum->where('departement_id', $departement_id);
                    }
                }
                $allowance_sum->get();

                //return "( ".$allowance_sum." ) د.ك";
                return $allowance_sum->sum("amount");
            })

            ->rawColumns(['allowance_all_count_but', 'allowance_part_count_but', 'allowance_sum_but'])
            //->rawColumns(['action'])
            
            ->with('total_amount', function() use ($total_amount) {
                return number_format($total_amount).' د.ك ';
            })

            ->make(true);
    }

    public function get_departement_with_all($sector_id, $type)
    {
        if($type == 1){
            $sector_details = Sector::where('uuid', $sector_id)->first();
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_details->id)->where('parent_id', null)->get();
        }else{
            $user = auth()->user();
            $get_departements = departements::where('id', '!=', 1)->where('id', $user->department_id)->get();
        }
        $departement_id = 0;

        return view('reservation_allowance.get_departements_with_all', compact('get_departements', 'departement_id'));
    }
    
    public function get_departement($sector_id, $type)
    {
        if($type == 1){
            $sector_details = Sector::where('uuid', $sector_id)->first();
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_details->id)->where('parent_id', null)->get();
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
                $reservation_allowance_type = departements::where('uuid', $department_id)->first()->reservation_allowance_type;
            }else{
                $reservation_allowance_type = Sector::where('uuid', $sector_id)->first()->reservation_allowance_type;
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
        $current_departement = departements::where('uuid', $department_id)->first();
        $current_sector = Sector::where('uuid', $sector_id)->first();

        foreach($civil_numbers as $civil_number){//file_number
           // $employee = User::where('Civil_number', $civil_number)->first();
           $employee = User::where('file_number', $civil_number)->first();

            if($employee){// check if employee
                if($employee->sector != $current_sector->id){
                    $check_sector++;
                }
                if($employee->department_id != $current_departement->id){
                    $check_department++;
                }
            }
        }

        return view('reservation_allowance.check_sector_department', compact('check_sector', 'check_department'));
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

    public function add_reservation_allowances_employes_id($uuid)
    {
        $get_employees = Cache(auth()->user()->id."_employee_new_add");
        if($get_employees != null){
            foreach($get_employees as $k_get_employee=>$get_employee){
                if($uuid === $get_employee){
                    unset($get_employees[$k_get_employee]);
                    $get_employees = array_values($get_employees);
                    Cache::put(auth()->user()->id."_employee_new_add",$get_employees);
                }
            }
        }
        $get_employees[] = $uuid; 
        Cache::put(auth()->user()->id."_employee_new_add", $get_employees);
        return Cache(auth()->user()->id."_employee_new_add");
        // Cache::forget(auth()->user()->id."_employee_new_add");
    }

    // public function add_reservation_allowances_employess($type, $id)
    // {
    //     $cache_key = auth()->user()->id;
    //     $cache_lock = Cache::lock('cache_lock_'.$cache_key, 10); // Lock for 10 seconds

    //     if ($cache_lock->get()) {
    //         $get_employees = Cache::get($cache_key);
    //         if ($get_employees != null) {
    //             foreach ($get_employees as $k_get_employee => $get_employee) {
    //                 if (in_array($id, $get_employees)) {
    //                     unset($get_employees[$k_get_employee]);
    //                     $get_employees = array_values($get_employees);
    //                     Cache::put($cache_key, $get_employees);
    //                 }

    //                 if ($type == 0) {
    //                     unset($get_employees[$k_get_employee]);
    //                     $get_employees = array_values($get_employees);
    //                     Cache::put($cache_key, $get_employees);
    //                 }
    //             }
    //         }

    //         if ($type != 0) {
    //             $get_employees[] = ['uuid' => $id, 'type' => $type];
    //             Cache::put($cache_key, $get_employees);
    //         }
    //         $cache_lock->release(); // Release the lock after update
    //     } else {
    //         // Handle the case where the lock couldn't be acquired
    //         return response()->json(['error' => 'Could not acquire cache lock'], 500);
    //     }

    //     return Cache::get($cache_key);
    // }

    public function add_reservation_allowances_employess($type, $id)
    {
        $get_employees = Cache::get(auth()->user()->id);
        if($get_employees != null){
            foreach($get_employees as $k_get_employee=>$get_employee){
                if(in_array($id, $get_employees)){
                    unset($get_employees[$k_get_employee]);
                    $get_employees = array_values($get_employees);
                    Cache::put(auth()->user()->id,$get_employees);
                }

                if($type == 0){
                    unset($get_employees[$k_get_employee]);
                    $get_employees = array_values($get_employees);
                    Cache::put(auth()->user()->id,$get_employees);
                }
            }
        }
        if($type != 0){
            $get_employees[] = ['uuid'=>$id, 'type'=>$type]; 
            Cache::put(auth()->user()->id, $get_employees);
        }
        return Cache::get(auth()->user()->id);
    }

    // public function add_reservation_allowances_employess($ids=array())
    // {
    //     Cache::put(auth()->user()->id, $ids);

        // $get_employees = Cache::get(auth()->user()->id);
        // if($get_employees != null){
        //     foreach($get_employees as $k_get_employee=>$get_employee){
        //         if(in_array($id, $get_employees)){
        //             unset($get_employees[$k_get_employee]);
        //             $get_employees = array_values($get_employees);
        //             Cache::put(auth()->user()->id,$get_employees);
        //         }

        //         if($type == 0){
        //             unset($get_employees[$k_get_employee]);
        //             $get_employees = array_values($get_employees);
        //             Cache::put(auth()->user()->id,$get_employees);
        //         }
        //     }
        // }
        // if($type != 0){
        //     $get_employees[] = ['uuid'=>$id, 'type'=>$type]; 
        //     Cache::put(auth()->user()->id, $get_employees);
        // }
        // return Cache::get(auth()->user()->id);
    //}

    public function view_reservation_allowances_employess()
    {
        //return Cache(auth()->user()->id."_employee_new_add");
        //Cache::forget(auth()->user()->id);
        return Cache::get(auth()->user()->id);
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

        Cache::forget(auth()->user()->id);

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
        $sectorId = 0;
        $departementId = 0;
        $get_departements = [];
        if($request->sector_id){
            $sectorId = $request->sector_id;
            $sector_id = Sector::where('uuid', $sectorId)->first()->id;
        }

        if($request->departement_id && $request->departement_id != "all"){
            $departementId = $request->departement_id;
            $departement_id = departements::where('uuid', $departementId)->first()->id;
            if($user->department_id == null){
                $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
            }else{
                //$user = auth()->user();
                $get_departements = departements::where('id', '!=', 1)->where('id', $user->department_id)->get();
            }
        }else{
            $departement_id = $request->departement_id;
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->get();  
        }

        $reservation_allowance_type = 0;
        if($sector_id != 0){
            if($departement_id != 0 && $departement_id != "all"){
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

            if($departement_id != "all"){
                if($departement_id != 0){
                    $data = $data->where('department_id', $departement_id);
                    $get_employee_reservation = $get_employee_reservation->where('departement_id', $departement_id);
                }else{
                    $data = $data->where('department_id', null);
                    $get_employee_reservation = $get_employee_reservation->where('departement_id', null);
                }
            }
            
            $get_employee_reservation = $get_employee_reservation->pluck('user_id');
            $data = $data->whereNotIn('id', $get_employee_reservation);
            $data = $data->get();
        }

        $employees = $data;
        return view('reservation_allowance.search_employee_new', compact('today' , 'departementId', 'sectorId','department_type', 'reservation_allowance_type', 'sector_id', 'departement_id', 'sectors', 'get_departements', 'employees'));
    }

    public function view_choose_reservation($date,$sectorId=0,$departementId=0)
    {      
        $current_departement = null;  
        if($departementId != 0){
            $current_departement = departements::where('uuid', $departementId)->first();
            $departement_id = $current_departement->id;
        }else{
            $departement_id = 0;
        }
        
        $current_sector = Sector::where('uuid', $sectorId)->first();
        $sector_id = $current_sector->id;

        $sector = $sector_id;
        $departement = $departement_id;
        $get_employee_for_all_reservations = [];
        $get_employee_for_part_reservations = [];

        $employee_amount = 0;
        $reservation_amout = 0;
        $reservation_amount_part = 0;
        $reservation_amount_all = 0;

        if(Cache::has(auth()->user()->id)){

            $user = auth()->user();
            $to_day = $date;
            //$to_day = Carbon::now()->format('Y-m-d');
            $to_day_name = Carbon::parse($date)->translatedFormat('l');
            $get_employees = Cache::get(auth()->user()->id);
            // $sector_id = 0;
            // $departement_id = 0;
            
            foreach($get_employees as $get_employee){
                $employee = User::where('uuid', $get_employee['uuid'])->first();

                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($get_employee['type'] == 1){
                            $grade_value = $employee->grade->value_all;
                            $get_employee_for_all_reservations[] = $employee;
                            $employee['grade_value'] = $grade_value;
                            $reservation_amount_all += $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                            $reservation_amount_part += $employee->grade->value_part;
                            $employee['grade_value'] = $grade_value;
                            $get_employee_for_part_reservations[] = $employee;
                        }
                        $employee_amount += $grade_value;
                    }
                }
            }


            //check to reservation month
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
             
        }

        //return $get_employee_for_all_reservations;

        return view('reservation_allowance.view_choose_reservation', compact('sectorId', 'departementId', 'reservation_amount_all', 'reservation_amount_part', 'get_employee_for_all_reservations', 'get_employee_for_part_reservations', 'date','current_departement', 'current_sector'));

    }

    public function confirm_reservation_allowances($date,$sectorId=0,$departementId=0)
    {
        $current_departement = null;  
        if($departementId != 0 && $departementId != "all"){
            $current_departement = departements::where('uuid', $departementId)->first();
            $department_id = $current_departement->id;
        }else{
            $department_id = null;
        }
        
        $current_sector = Sector::where('uuid', $sectorId)->first();
        $sector_id = $current_sector->id;


        $sector = $sector_id;
        $departement = $department_id;
        $datey = $date;

        if(Cache::has(auth()->user()->id)){

            $user = auth()->user();
            $to_day = $date;
            //$to_day = Carbon::now()->format('Y-m-d');
            $to_day_name = Carbon::parse($date)->translatedFormat('l');
            $get_employees = Cache::get(auth()->user()->id);
            $employee_amount = 0;
            $reservation_amout = 0;
            $sector_mandate = null;
            $department_mandate = null;

            $type_departement = 1;
            $reservation_amout = sector::where('id', $sector_id)->first()->reservation_allowance_amount;
            if($department_id != null && $department_id != "all"){
                $type_departement = 2;
                $reservation_amout = departements::where('id', $department_id)->first()->reservation_allowance_amount;
            }


            $first_day = date('Y-m-01');
            $last_day = date('Y-m-t');

            //add ReservationAllowance
            foreach($get_employees as $get_employee){

                $employee = User::where('uuid', $get_employee['uuid'])->first();
                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($get_employee['type'] == 1){
                            $grade_value = $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                        }

                        $type_departement = 1;
                        if($employee->department_id == null){
                            $type_departement = 2;
                        }

                        $check_reservation_allowance = ReservationAllowance::where(['user_id' => $employee->id, 'date' => $to_day])->first();
                        if(!$check_reservation_allowance){
                            //return redirect()->back()->with('error','عفوا تم اضافة بدل لحجز '.$employee->name.' فى هذا اليوم من قبل');
                            $add_reservation_allowance = new ReservationAllowance();
                            $add_reservation_allowance->user_id = $employee->id;
                            $add_reservation_allowance->type = $get_employee['type'];
                            $add_reservation_allowance->amount = $grade_value;
                            $add_reservation_allowance->date = $to_day;
                            $add_reservation_allowance->day = $to_day_name;
                            $add_reservation_allowance->type_departement = $type_departement;

                            $add_reservation_allowance->sector_id  = $sector_id;
                            $add_reservation_allowance->departement_id = $department_id;

                            //$add_reservation_allowance->department_mandate = ($department_id != null ? $employee->department_id : $department_id);
                            $add_reservation_allowance->grade_id = $employee->grade_id;
                            $add_reservation_allowance->created_by = auth()->user()->id;

                            if($employee->sector != $sector_id){
                                $sector_mandate = $sector_id;
                                $add_reservation_allowance->sector_mandate = $employee->sector;
                            }
    
                            if($department_id != null){
                                if($employee->department_id != $department_id){
                                    $department_mandate = $department_id;
                                    $add_reservation_allowance->department_mandate = $employee->department_id;
                                }
                            }else{
                                if($employee->department_id != null){
                                    $sector_mandate = $sector_id;
                                    $add_reservation_allowance->sector_mandate = $employee->sector;
                                    $add_reservation_allowance->department_mandate = $employee->department_id;
                                }
                            }

                            if($sector_mandate != null || $department_mandate != null){
                                $add_reservation_allowance->mandate = 1;
                            }
    
                            $add_reservation_allowance->save();

                            $sector_mandate = null;
                            $department_mandate = null;
                        }

                    }
                }
            }
            Cache::forget(auth()->user()->id);
        }
        //return redirect()->route('reservation_allowances.index')->with('success', 'تم اضافه بدل حجز بنجاح');
        return redirect()->route('reservation_allowances.index_data',[$sectorId, $departementId, $datey])->with('success', 'تم اضافه بدل حجز بنجاح');
    }

    public function details($uuid, $sector_ids, $departement_ids, $month, $year, $type)
    {
        //try{
            $user_gest = auth()->user();
            $sectorDetails = "";
            $departementDetails = "";
            
            //check sector
            if($sector_ids){
                $sectorId = $sector_ids;
                $sectorDetails = Sector::where('uuid', $sectorId)->first();
                if($sectorDetails){
                    $sector_id = $sectorDetails->id;
                }else{
                    $sector_id = 0;
                }
            }else{
                $sector_id = 0;
            }

            //check department
            if($departement_ids){
                $departementId = $departement_ids;
                $departementDetails = departements::where('uuid', $departementId)->first();
                if($departementDetails){
                    $departement_id = $departementDetails->id;
                }else{
                    $departement_id = 0;
                }
            }else{
                $departement_id = 0;
            }

            if($sector_id == 0){
                if($user_gest->rule_id != 2){
                    $sector_id = $user_gest->sector;
                }
            }

            if($departement_id == 0){
                if($user_gest->rule_id == 3){
                    $departement_id = $user_gest->department_id;
                }
            }

            $employee = User::where('uuid', $uuid)->first();
            $allowance_results = ReservationAllowance::where('user_id', $employee->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('type', $type);
                if($sector_id != 0){
                    $allowance_results->where('sector_id', $sector_id);
                }else{
                    if($user_gest->rule_id != 2){
                        $allowance_results->where('sector_id', $sector_id);
                    }
                }
                if($departement_id != 0){
                    $allowance_results->where('departement_id', $departement_id);
                }else{
                    if($user_gest->rule_id == 3){
                        $allowance_results->where('departement_id', $departement_id);
                    }
                }
            $get_allowance_results = $allowance_results->get();
            $total = $get_allowance_results->sum('amount');

            return view('reservation_allowance.reservation_details', compact('employee','get_allowance_results' ,'sectorDetails', 'departementDetails', 'year', 'month', 'type', 'total'));

        // }catch(\Exception $e){
        //     return redirect()->back()->with('error', 'An error occurred while creating the group. Please try agai');
        // }

    }

    public function printReport($date,$sectorId=0,$departementId=0)
    {      
        $current_departement = null;  
        if($departementId != 0){
            $current_departement = departements::where('uuid', $departementId)->first();
            $departement_id = $current_departement->id;
        }else{
            $departement_id = null;
        }
        
        $current_sector = Sector::where('uuid', $sectorId)->first();
        $sector_id = $current_sector->id;

        $sector = $sector_id;
        $departement = $departement_id;
        $get_employee_for_all_reservations = [];
        $get_employee_for_part_reservations = [];

        $employee_amount = 0;
        $reservation_amout = 0;
        $reservation_amount_part = 0;
        $reservation_amount_all = 0;

        if(Cache::has(auth()->user()->id)){

            $user = auth()->user();
            $to_day = $date;
            //$to_day = Carbon::now()->format('Y-m-d');
            $to_day_name = Carbon::parse($date)->translatedFormat('l');
            $get_employees = Cache::get(auth()->user()->id);
            // $sector_id = 0;
            // $departement_id = 0;
            
            foreach($get_employees as $get_employee){
                $employee = User::where('uuid', $get_employee['uuid'])->first();

                if($employee){// check if employee
                    if($employee->grade_id != null){ // check if employee has grade
                        if($get_employee['type'] == 1){
                            $grade_value = $employee->grade->value_all;
                            $get_employee_for_all_reservations[] = $employee;
                            $employee['grade_value'] = $grade_value;
                            $reservation_amount_all += $employee->grade->value_all;
                        }else{
                            $grade_value = $employee->grade->value_part;
                            $reservation_amount_part += $employee->grade->value_part;
                            $employee['grade_value'] = $grade_value;
                            $get_employee_for_part_reservations[] = $employee;
                        }
                        $employee_amount += $grade_value;
                    }
                }
            }

        }
 
        $data = [
            'date' => $date,
            'sector' => $current_sector->name,
            'department' => $current_departement != null ? $current_departement->name : "",
            'get_employee_for_all_reservations' => $get_employee_for_all_reservations,
            'get_employee_for_part_reservations' => $get_employee_for_part_reservations,
            'reservation_amount_all' => $reservation_amount_all,
            'reservation_amount_part' => $reservation_amount_part
        ];

        //return $data;
            
        // Generate PDF
        $pdf = $this->generatePDF($data);
        return $pdf->Output('reservation_report.pdf', 'I');
    }
    
    private function generatePDF($data)
    {
        $pdf = new TCPDF();
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your App');
        $pdf->SetTitle('Reservation Report');
        $pdf->SetSubject('Report');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->AddPage();
        $pdf->setRTL(true);
        $html = view('reservation_allowance.print', $data)->render();
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        return $pdf;
    }
}
