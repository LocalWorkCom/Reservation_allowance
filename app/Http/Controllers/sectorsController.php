<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\departements;
use App\Models\Government;
use App\Models\Rule;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class sectorsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
         if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
            $sectors = Sector::all();

         }elseif(Auth::user()->rule->id == 4){
            $sectors = Sector::where('id',auth()->user()->sector);

         }
        return view("sectors.index");
    }

    public function getsectors()
    {
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id== 2) {
            $data = Sector::all();
         }elseif(Auth::user()->rule->id == 4){
            $data = Sector::where('id',auth()->user()->sector);
         }
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $edit_permission = '<a class="btn btn-sm" style="background-color: #F7AF15;" href=' . route('sectors.edit', $row->id) . '><i class="fa fa-edit"></i> تعديل</a>';
                $add_permission = '<a class="btn btn-sm" style="background-color: #274373;" href="' .  route('department.create', ['id' => $row->id]) . '"><i class="fa fa-plus"></i> أضافة أداره</a>';
                $show_permission = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('sectors.show', $row->id) . '> <i class="fa fa-eye"></i>عرض</a>';

                return $show_permission . ' ' . $edit_permission . '' . $add_permission;
            })
            ->addColumn('manager_name', function ($row) {
                return $row->manager_name->name ?? 'لا يوجد مدير';
            })
            ->addColumn('departments', function ($row) {
                $num = departements::where('sector_id', $row->id)->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373; padding-inline: 15px" href=' . route('departments.index', ['id'=>$row->id]) . '> ' . $num . '</a>';

                return $btn;
            })
            ->addColumn('reservation_allowance_amount', function ($row) {
                return $row->reservation_allowance_amount;
            })
            ->addColumn('reservation_allowance', function ($row) {
                if ($row->reservation_allowance_type == 1) {
                    return 'حجز كلى';
                } elseif ($row->reservation_allowance_type == 2) {
                    return 'حجز جزئى';
                } else {
                    return 'حجز كلى و حجز جزئى';
                }
            })
            ->addColumn('employees', function ($row) {
                $emp_num = User::where('sector', $row->id)->where('department_id', null)->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373; padding-inline: 15px" href=' . route('departments.index', ['id'=>$row->id]) . '> ' . $emp_num . '</a>';
                return $btn;
            })
            ->addColumn( 'employeesdep', function ($row) {
                $emp_num = User::where('sector', $row->id)->whereNotNull('department_id')->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373; padding-inline: 15px" href=' . route('departments.index', ['id'=>$row->id]) . '> ' . $emp_num . '</a>';

                return $btn;
            })
            ->rawColumns(['action', 'departments','employees', 'employeesdep'])
            ->make(true);
    }


    public function create()
    {
        $users = User::where('department_id', null)->where('sector', null)->get();
        $rules = Rule::where('name', 'sector manager')->get();
        return view('sectors.create', compact('users', 'rules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'mangered.required' => 'اسم المدير مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.00.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mangered' => 'required',
            'budget' => 'required|numeric|min:0.00|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Convert Civil_numbers input into an array
        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = explode(',,', $Civil_numbers);

        // Find employees based on Civil_number
        $employees = User::whereIn('Civil_number', $Civil_numbers)->pluck('id')->toArray();

        // Check if the selected manager is one of the employees
        if (in_array($request->mangered, $employees)) {
            return redirect()->back()->with('error', 'المدير لا يمكن أن يكون أحد الموظفين المدرجين.');
        }

        // Handle reservation allowance type
        $part = $request->input('part');
        $reservation_allowance_type = null;

        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3;
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1;
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2;
        }

        // Save sector details
        $sector = new Sector();
        $sector->name = $request->name;
        $sector->reservation_allowance_type = $reservation_allowance_type;
        $sector->reservation_allowance_amount = $request->budget;
        $sector->manager = $request->mangered;
        $sector->created_by = Auth::id();
        $sector->updated_by = Auth::id();
        $sector->save();

        // Handle manager update (with or without password)
        $user = User::find($request->mangered);
        if ($user) {
            $user->sector = $sector->id;
            $user->department_id = null;
            if ($request->password) {
                $user->flag = 'user';
                $user->rule_id = $request->rule;
                $user->password = Hash::make($request->password);
            }
            $user->save();
            // Optionally send email notification
        } else {
            return redirect()->back()->with('error', 'هذا المستخدم غير موجود');
        }

        // Track Civil numbers that could not be added
        $failed_civil_numbers = [];

        // Update employees in the sector
        foreach ($Civil_numbers as $Civil_number) {
            $employee = User::where('Civil_number', $Civil_number)->first();
            if ($employee && $employee->grade_id != null) {
                $employee->sector = $sector->id;
                $employee->department_id = null;
                $employee->save();
            } else {
                // Add Civil_number to the failed list if the employee is not found or has no grade_id
                $failed_civil_numbers[] = $Civil_number;
            }
        }
        // Prepare success message
        $message = 'تم أضافه قطاع جديد';
        // Append failed Civil numbers to the message, if any
        if (count($failed_civil_numbers) > 0) {
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم العثور على الأرقام المدنية أو عدم وجود درجة لهم: ' . implode(', ', $failed_civil_numbers);
        }

        return redirect()->route('sectors.index')->with('message', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Sector::find($id);
        $users = User::where('department_id', null)->whereNot('id',$data->manager)->Where('sector', $id)->get();
        $departments = departements::where('sector_id', $id)->get();
        return view('sectors.showdetails', compact('data', 'users', 'departments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Find the sector being edited
        $data = Sector::findOrFail($id);
        $users = User::where('department_id', null)->where('sector', null)->orWhere('sector', $id)->get();
        $employees =  User::where('department_id', null)->Where('sector', $id)->whereNot('id', $data->manager)->get();
        $rules = Rule::whereNotIn('id', [1, 2])->get();
        return view('sectors.edit', [
            'data' => $data,
            'users' => $users,
            'employees' => $employees,
            'rules' => $rules
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $sector = Sector::find($request->id);
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'mangered.required' => 'اسم المدير مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.00.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Retrieve the old manager before updating
        $oldManager = $sector->manager;

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mangered' => 'required',
            'budget' => 'required|numeric|min:0.00|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Determine reservation_allowance_type based on 'part'
        $part = $request->input('part');
        $reservation_allowance_type = null;
        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3;
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1;
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2;
        }

        // Update sector details
        $sector->name = $request->name;
        $sector->reservation_allowance_type = $reservation_allowance_type;
        $sector->reservation_allowance_amount = $request->budget;
        $sector->manager = $request->mangered;
        $sector->updated_by = Auth::id();
        $sector->save();
        // Check if manager has changed
        if ($oldManager != $request->mangered) {
            // Update old manager's sector to null
            if ($oldManager) {
                $oldManagerUser = User::find($oldManager);
                if ($oldManagerUser) {
                    $oldManagerUser->sector = Null;
                    $oldManagerUser->flag = 'employee';
                    $oldManagerUser->password = Null;
                    $oldManagerUser->save();
                }
            }
            // Update new manager's sector
            if ($request->password) {
                $newManager = User::find($request->mangered);
                if ($newManager) {
                    $newManager->sector = $sector->id;
                    $newManager->flag = 'user';
                    $newManager->department_id = Null;
                    $newManager->rule_id = $request->rule;
                    $newManager->password = Hash::make($request->password);
                    $newManager->save();
                } else {
                    return redirect()->back()->with('خطأ', 'هذا المستخدم غير موجود');
                }
            } else {
                $user = User::find($request->mangered);
                if ($user) {
                    $user->sector = $sector->id;
                    $user->department_id = Null;
                    $user->save();
                } else {
                    return redirect()->back()->with('خطأ', 'هذا المستخدم غير موجود');
                }
            }
        }

        // Handle employee Civil_number updates
        $currentEmployees = User::where('sector', $sector->id)
            ->where('department_id', null)
            ->pluck('Civil_number')
            ->toArray();

        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = array_filter(explode(',,', $Civil_numbers));

        $employeesToRemove = array_diff($currentEmployees, $Civil_numbers);

        $employeesToAdd = array_diff($Civil_numbers, $currentEmployees);

        if (!empty($employeesToRemove)) {
            User::whereIn('Civil_number', $employeesToRemove)->update(['sector' => null, 'department_id' => null]);
        }

        foreach ($employeesToAdd as $Civil_number) {
            $employee = User::where('Civil_number', $Civil_number)->first();
            if ($employee && $employee->grade_id != null) {
                $employee->sector = $sector->id;
                $employee->save();
            }
        }

        return redirect()->route('sectors.index')->with('message', 'تم تحديث القطاع والموظفين بنجاح.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
