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
        $employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();

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
            $data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
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
        //try{
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
        /*}catch(\Exception $e){
            return redirect()->back()->with('error', 'An error occurred while creating the group. Please try agai');
        }*/
    }

    public function create_all()
    {
        $to_day = Carbon::now()->format('Y-m-d');
        $to_day_name = Carbon::now()->translatedFormat('l');
        $user = auth()->user();
        $super_admin = User::where('department_id', 1)->first();
        $employees = User::where('department_id', $user->department_id)->where('flag', 'employee')->get();

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
            $data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
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

    /**
     * Store a newly created resource in storage.
     */
    public function store_all(Request $request)
    {
        //try{
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

            $Civil_numbers = str_replace(array("\r","\r\n","\n"),',',$request->Civil_number);
            $Civil_numbers = explode(',,',$Civil_numbers);

            foreach($Civil_numbers as $Civil_number){

                $employee = User::where('Civil_number', $Civil_number)->first();
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
                            $add_reservation_allowance->sector_id = $employee->sector;
                            $add_reservation_allowance->departement_id = $employee->department_id;
                            $add_reservation_allowance->grade_id = $employee->grade_id;
                            $add_reservation_allowance->created_by = $user->id;
                            $add_reservation_allowance->save();
                        }
                    }
                }
            }

            return redirect()->route('reservation_allowances.index')->with('success', 'تم اضافه بدل حجز بنجاح');
        /*}catch(\Exception $e){
            return redirect()->back()->with('error', 'An error occurred while creating the group. Please try agai');
        }*/
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

        /*if($user->rule_id == 2)
        {
            $data = ReservationAllowance::with('users', 'users.grade', 'departements')->where('date', $to_day)->get();
        }else{
            if($user->department->children->count() > 0){
                $department_id = $user->department->children->pluck('id');
            }else{
                $department_id[] = $user->department_id;
            }
            $data = ReservationAllowance::with('users', 'users.grade', 'departements')->whereIn('departement_id', $department_id)->where('date', $to_day)->get();
        }*/

        //$data = [];

        $data = ReservationAllowance::with('users', 'users.grade', 'departements')->where('date', $to_day)->get();


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
            ->addColumn('type', function ($row) {
                return $row->type;
            })
            ->addColumn('employee_allowance_type_btn', function ($row) {
                if($row->type == '1'){
                    $btn = '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" class="form-control" checked disabled></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio" class="form-control" disabled></div></div>';
                }else{
                    $btn = '<div class="d-flex" style="justify-content: space-around !important"><div style="display: inline-flex; direction: ltr;"><label for="">  حجز كلى</label><input type="radio" class="form-control" disabled></div><span>|</span><div style="display: inline-flex; direction: ltr;"><label for="">  حجز جزئى</label><input type="radio"class="form-control" checked disabled></div></div>';
                }
                return $btn;
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
        foreach($civil_numbers as $civil_number){
            $employee = User::where('Civil_number', $civil_number)->first();
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

        /*if($sector_id == 0){
            return redirect()->back()->with('error','عفوا يجب اختيار اسم القطاع');
        }*/

        if($user->department_id == null){
            $get_departements = departements::where('id', '!=', 1)->where('sector_id', $sector_id)->where('parent_id', null)->get();
        }else{
            $user = auth()->user();
            $get_departements = departements::where('id', '!=', 1)->where('id', $user->department_id)->get();
        }

        $data = [];

        if($sector_id != 0){
            $data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
            if($departement_id != 0){
                $data = $data->where('department_id', $departement_id);
            }else{
                $data = $data->where('department_id', null);
            }
            $data = $data->get();
        }

        $employees = $data;

        return view('reservation_allowance.search_employee_new', compact('department_type', 'sector_id', 'departement_id', 'sectors', 'get_departements', 'employees'));
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
            $data = User::query()->where('sector', $sector_id)->where('flag', 'employee');
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
                return $row->amount;  // Display the count of iotelegrams
            })

            ->rawColumns(['employee_allowance_type_btn'])
            //->rawColumns(['action'])
            ->make(true);
    }

    public function add_reservation_allowances_employess($type, $id)
    {
        $arr = Cache::get(auth()->user()->id);
        $arr[] = ['id'=>$id, 'type'=>$type];
        Cache::put(auth()->user()->id, $arr);

        return Cache::get(auth()->user()->id);
    }

    public function view_reservation_allowances_employess()
    {
        return Cache::get(auth()->user()->id);
    }

    public function confirm_reservation_allowances()
    {
        if(Cache::has(auth()->user()->id)){

            $user = auth()->user();
            $to_day = Carbon::now()->format('Y-m-d');
            $to_day_name = Carbon::now()->translatedFormat('l');
            $get_employees = Cache::get(auth()->user()->id);
            $employee_amount = 0;
            $reservation_amout = 0;
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

                        $type_departement = 1;
                        $reservation_amout = departements::where('id', $employee->department_id)->first()->reservation_allowance_amount;
                        if($employee->department_id == null){
                            $type_departement = 2;
                            $reservation_amout = departements::where('id', $employee->sector)->first()->reservation_allowance_amount;
                        }

                    }
                }
            }

            if($reservation_amout <= $employee_amount){
                return redirect()->back()->with('error','عفوا لقد تجاوزت ملبغ بدل الحجز');
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

                        $type_departement = 1;
                        if($employee->department_id == null){
                            $type_departement = 2;
                        }

                        $check_reservation_allowance = ReservationAllowance::where(['user_id' => $employee->id, 'date' => $to_day])->first();
                        if(!$check_reservation_allowance){
                            if($get_employee['type'] != 0){
                                //return redirect()->back()->with('error','عفوا تم اضافة بدل لحجز '.$employee->name.' فى هذا اليوم من قبل');
                                $add_reservation_allowance = new ReservationAllowance();
                                $add_reservation_allowance->user_id = $employee->id;
                                $add_reservation_allowance->type = $get_employee['type'];
                                $add_reservation_allowance->amount = $grade_value;
                                $add_reservation_allowance->date = $to_day;
                                $add_reservation_allowance->day = $to_day_name;
                                $add_reservation_allowance->sector_id = $employee->sector;
                                $add_reservation_allowance->type_departement = $type_departement;
                                $add_reservation_allowance->departement_id = $employee->department_id;
                                $add_reservation_allowance->grade_id = $employee->grade_id;
                                $add_reservation_allowance->created_by = $user->id;
                                $add_reservation_allowance->save();
                            }
                        }
                    }
                }
            }
            Cache::forget(auth()->user()->id);
        }
        return redirect()->route('reservation_allowances.index')->with('success', 'تم اضافه بدل حجز بنجاح');
    }
}
