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
        $sectors = Sector::all();
        return view("sectors.index");
    }

    public function getsectors()
    {
        $data = Sector::all();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $edit_permission = '<a class="btn btn-sm" style="background-color: #F7AF15;" href=' . route('sectors.edit', $row->id) . '><i class="fa fa-edit"></i> تعديل</a>';
                $add_permission = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('departments.create', $row->id) . '><i class="fa fa-plus"></i> أضافة أداره</a>';
                $show_permission = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('sectors.show', $row->id) . '> <i class="fa fa-eye"></i>عرض</a>';

                return $show_permission . ' ' . $edit_permission . '' . $add_permission;
            })
            ->addColumn('manager_name', function ($row) {
                return $row->manager_name->name ?? 'لا يوجد مدير';
            })
            ->addColumn('departments', function ($row) {
                $num = departements::where('sector_id', $row->id)->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373; padding-inline: 15px" href=' . route('departments.index', $row->id) . '> ' . $num . '</a>';

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
                return $emp_num;
            })
            ->rawColumns(['action', 'departments']) // Add 'departments' to rawColumns
            ->make(true);
    }



    public function create()
    {
        $users = User::where('department_id', null)->where('sector', null)->get();
        $rules = Rule::whereNotIn('id', [1, 2])->get();
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
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.01.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mangered' => 'required',
            'budget' => 'required|numeric|min:0.01|max:1000000',
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

        // Update employees in the sector
        foreach ($Civil_numbers as $Civil_number) {
            $employee = User::where('Civil_number', $Civil_number)->first();
            if ($employee && $employee->grade_id != null) {
                $employee->sector = $sector->id;
                $employee->department_id = null;
                $employee->save();
            }
        }

        return redirect()->route('sectors.index')->with('message', 'تم أضافه قطاع جديد');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Sector::find($id);
        $users = User::where('department_id', null)->Where('sector', $id)->get();
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
        $users = User::where('department_id', null)->orWhere('id', $data->manager)->get();
        $employees = User::where('sector', $id)->where('department_id', null)->whereNot('id', $data->manager)->get();


        return view('sectors.edit', [
            'data' => $data,
            'users' => $users,
            'employees' => $employees
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $sector = Sector::find($request->id); // Assuming $sectorId is passed or available
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'mangered.required' => 'اسم المدير مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.01.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Retrieve the old manager before updating
        $oldManager = $sector->manager;

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mangered' => 'required',
            'budget' => 'required|numeric|min:0.01|max:1000000',
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
                    $oldManagerUser->sector = null;
                    $oldManagerUser->flag = 'employee';
                    $oldManagerUser->password = null;
                    $oldManagerUser->save();
                }
            }
            // Update new manager's sector
            if ($request->password) {
                $newManager = User::find($request->mangered);
                if ($newManager) {
                    $newManager->sector = $sector->id;
                    $newManager->flag = 'user';
                    $newManager->department_id = null;
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
                    $user->department_id = null;
                    $user->save();
                } else {
                    return redirect()->back()->with('خطأ', 'هذا المستخدم غير موجود');
                }
            }
        }

        // Handle employee Civil_number updates

        // Get the current employees associated with this sector
        $currentEmployees = User::where('sector', $sector->id)->where('department_id', null)->pluck('Civil_number')->toArray();

        // Get the new employees from the request
        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = array_filter(explode(',,', $Civil_numbers)); // Filter out any empty values

        // Employees to be removed (who are in the current sector but not in the new request)
        $employeesToRemove = array_diff($currentEmployees, $Civil_numbers);

        // Set the sector to null for removed employees
        if (!empty($employeesToRemove)) {
            User::whereIn('Civil_number', $employeesToRemove)->update(['sector' => null, 'department_id' => null]);
        }

        // Update or assign the new employees to this sector
        foreach ($Civil_numbers as $Civil_number) {
            $employee = User::where('Civil_number', $Civil_number)->first();
            if ($employee && $employee->grade_id != null) {
                $employee->sector = $sector->id;
                $employee->save();
            }
        }

        return redirect()->route('sectors.index')->with('message', 'تم تعديل القطاع');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
