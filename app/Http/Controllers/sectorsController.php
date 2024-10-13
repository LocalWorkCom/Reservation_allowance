<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\departements;
use App\Models\Government;
use App\Models\Rule;
use App\Models\Sector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

class sectorsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function getManagerSectorDetails($id, $sector)
    {
        // Fetch manager data from the database file_number
        // $manager = User::where('Civil_number', $id)->first();
        $manager = User::where('file_number', $id)->first();

        if ($sector === 'null') {
            $sector = null;
        }
        if (!$manager) {
            return response()->json(['error' => 'عفوا هذا المستخدم غير موجود'], 404);
        }

        // Allow this check only for input change, not for initial load
        $isDepartmentCheck = request()->has('check_department') && request()->get('check_department') == true;

        // Ensure the manager is not assigned to another sector or department
        if ($isDepartmentCheck && ($manager->department_id != null || $manager->sector != null || ($sector != $manager->sector && $manager->sector != null))) {

            return response()->json(['error' => 'هذا المستخدم موجود فى قطاع مسبقا . هل تريد نقله ?'], 404);
        }

        // Calculate seniority/years of service
        $joiningDate = $manager->joining_date ? Carbon::parse($manager->joining_date) : Carbon::parse($manager->created_at);
        $today = Carbon::now();
        $yearsOfService = $joiningDate->diffInYears($today);

        // Check if the user is an employee (flag 'employee' means employee)
        $isEmployee = $manager->flag == 'employee';

        // Return the manager data in JSON format
        return response()->json([
            'rank' => $manager->grade_id ? $manager->grade->name : 'لا يوجد رتبه',
            'job_title' => $manager->job_title ?? 'لا يوجد مسمى وظيفى',
            'seniority' => $yearsOfService,
            'name' => $manager->name,
            'phone' => $manager->phone,
            'email' => $manager->email,
            'isEmployee' => $isEmployee,
        ]);
    }
    public function index()
    {
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
            $sectors = Sector::all();
        } elseif (Auth::user()->rule->id == 4) {
            $sectors = Sector::where('id', auth()->user()->sector);
        }
        return view("sectors.index");
    }

    public function getsectors()
    {
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
            $data = Sector::all();
        } elseif (Auth::user()->rule->id == 4) {
            $data = Sector::where('id', auth()->user()->sector);
        }
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $edit_permission = '<a class="btn btn-sm" style="background-color: #F7AF15;" href=' . route('sectors.edit', $row->id) . '><i class="fa fa-edit"></i> تعديل</a>';
                $add_permission = '<a class="btn btn-sm" style="background-color: #bb5207;" href="' .  route('department.create', ['id' => $row->id]) . '"><i class="fa fa-plus"></i> أضافة أداره</a>';
                $reservationAllowence = '<a class="btn btn-sm" style="background-color: #1d88a1;" href=' . route('reservation_allowances.search_employee_new', 'sector_id=' . $row->id) . '><i class="fa fa-plus"></i> اضافة بدل حجز جماعى</a>';
                $show_permission = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('sectors.show', $row->id) . '> <i class="fa fa-eye"></i>عرض</a>';
                // $addbadal_permission = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('sectors.show', $row->id) . '> <i class="fa fa-plus"></i>أضافه بدل</a>';

                return $show_permission . ' ' . $edit_permission . '' . $add_permission . ' ' . $reservationAllowence;
            })
            ->addColumn('manager_name', function ($row) {
                // Check if manager exists before accessing its attributes
                $manager = User::find($row->manager);
                if ($manager) {
                    // Check the flag to determine if the manager is an employee
                    $is_allow = $manager->flag == 'employee' ? 'لا يسمح بالدخول' : 'يسمح بالدخول';
                    // Return the manager's name along with the access permission status
                    return $manager->name . ' (' . $is_allow . ')';
                }
                return 'لا يوجد مدير';
            })
            ->addColumn('departments', function ($row) {
                $num = departements::where('sector_id', $row->id)->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('departments.index', ['id' => $row->id]) . '> ' . $num . '</a>';
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
                $btn = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('user.employees', ['sector_id' => $row->id, 'type' => 0]) . '> ' . $emp_num . '</a>';
                return $btn;
            })
            ->addColumn('employeesdep', function ($row) {
                $emp_num = User::where('sector', $row->id)->whereNotNull('department_id')->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373; padding-inline: 15p" href=' . route('user.employees', ['sector_id' => $row->id, 'type' => 1]) . '> ' . $emp_num . '</a>';
                return $btn;
            })
            ->rawColumns(['action', 'departments', 'employees', 'employeesdep'])
            ->make(true);
    }
    public function getManagerDetails($id)
    {
        // Fetch manager data from the database//file_number
        // $manager = User::where('Civil_number', $id)->first();
        $manager = User::where('file_number', $id)->first();

        if (!$manager) {
            return response()->json(['error' => 'عفوا هذا المستخدم غير موجود'], 404);
        }

        // Allow this check only for input change, not for initial load
        $isDepartmentCheck = request()->has('check_department') && request()->get('check_department') == true;

        // Check if the manager is assigned to a sector
        if ($isDepartmentCheck) {
            if ($manager->department_id != null || $manager->sector != null) {
                return response()->json(['confirm' => 'عفوا هذا المستخدم لديه قطاع بالفعل. هل أنت متأكد أنك تريد نقل هذا المستخدم إلى قسم آخر؟'], 200);
            }
        }

        // Calculate seniority/years of service
        $joiningDate = $manager->joining_date ? Carbon::parse($manager->joining_date) : Carbon::parse($manager->created_at);
        $today = Carbon::now();
        $yearsOfService = $joiningDate->diffInYears($today);

        // Check if the user is an employee (flag 'employee' means employee)
        $isEmployee = $manager->flag == 'employee';

        // Return the manager data in JSON format
        return response()->json([
            'rank' => $manager->grade_id ? $manager->grade->name : 'لا يوجد رتبه',
            'job_title' => $manager->job_title ?? 'لا يوجد مسمى وظيفى',
            'seniority' => $yearsOfService,
            'name' => $manager->name,
            'phone' => $manager->phone,
            'email' => $manager->email,
            'isEmployee' => $isEmployee,  // Include the employee flag
        ]);
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
        // Custom error messages for validation
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.00.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'required|numeric|min:0.00|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Process Civil_numbers input into an array
        $Civil_numbers = str_replace(["\r", "\r\n", "\n"], ',', $request->Civil_number);
        $Civil_numbers = explode(',,', $Civil_numbers);

        // Find employees based on Civil_number   file_number
        // $employees = User::whereIn('Civil_number', $Civil_numbers)->pluck('id')->toArray();
        $employees = User::whereIn('file_number', $Civil_numbers)->pluck('id')->toArray();

        // Initialize manager variable
        $manager = null;

        // Handle the case if `mangered` is provided
        if ($request->mangered) {
            // Find manager based on Civil Number
            // $manager = User::where('Civil_number', $request->mangered)->value('id');
            $manager = User::where('file_number', $request->mangered)->value('id');

            // Validate manager: must not be one of the employees
            if (in_array($manager, $employees)) {
                return redirect()->back()->withErrors('المدير لا يمكن أن يكون أحد الموظفين المدرجين.')->withInput();
            }

            // Validate if the manager exists
            if ($manager === null) {
                return redirect()->back()->withErrors('رقم هويه المدير غير موجود')->withInput();
            }
        }

        // Determine reservation allowance type
        $part = $request->input('part');
        $reservation_allowance_type = null;

        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3;
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1;
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2;
        }

        // Create and save new sector
        $sector = new Sector();
        $sector->name = $request->name;
        $sector->reservation_allowance_type = $reservation_allowance_type;
        $sector->reservation_allowance_amount = $request->budget;
        $sector->manager = $manager;
        $sector->created_by = Auth::id();
        $sector->updated_by = Auth::id();
        $sector->save();

        // Handle updating the manager, if present
        if ($manager) {
            $user = User::find($manager);

            if (!$user) {
                return redirect()->back()->with('error', 'هذا المستخدم غير موجود');
            }

            if ($user->sector != $sector->id || $user->sector != null) {
                // dd($user->sector);
                $old_sector = Sector::find($user->sector);

                if ($old_sector) { // Ensure old sector exists
                    $old_sector->manager = null;
                    $old_sector->save();
                }
            }

            // Continue with updating the user
            $user->sector = $sector->id;
            $user->department_id = null;

            // Update password and role if provided
            if ($request->password) {
                $user->flag = 'user';
                $user->rule_id = $request->rule;
                $user->password = Hash::make($request->password);
            }

            $user->save();
            Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع ' . $request->name, $user->file_number, $request->password ? $request->password : 'عفوا لن يتم السماح لك بدخول السيستم', $user->email);
        }

        // Track Civil numbers that could not be added
        $failed_civil_numbers = [];

        // Update employees in the sector
        foreach ($Civil_numbers as $Civil_number) {
            $employee = User::where('file_number', $Civil_number)->first();
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

        // Redirect to sectors index with success message
        return redirect()->route('sectors.index')->with('message', $message);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Sector::find($id);
        $users = User::where('department_id', null)->whereNot('id', $data->manager)->Where('sector', $id)->get();
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
        $rules = Rule::whereNotIn('id', [1, 2, 3])->get();
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
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.00.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'required|numeric|min:0.00|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve the old manager before updating
        $oldManager = $sector->manager;
        $manager = $request->mangered ? User::where('file_number', $request->mangered)->value('id') : null;

        // If a new manager is provided but not found in the system
        if ($request->mangered && $manager == null) {
            return redirect()->back()->withErrors('رقم هويه المدير غير موجود')->withInput();
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
        $sector->manager = $manager;
        $sector->updated_by = Auth::id();
        $sector->save();

        // Handle old and new manager updates
        if ($oldManager != $manager) {
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
            if ($manager) {
                $newManager = User::find($manager);
                if ($newManager->sector != $sector->id || $newManager->sector != null) {
                    $old_sector = Sector::find($newManager->sector);
                    if ($old_sector) {
                        $old_sector->manager = null;
                        $old_sector->save();
                    }
                }
                if ($newManager) {
                    $newManager->sector = $sector->id;
                    $newManager->department_id = null;

                    if ($request->password) {
                        $newManager->flag = 'user';
                        $newManager->rule_id = $request->rule;
                        $newManager->password = Hash::make($request->password);
                    }
                    $newManager->save();
                    Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $newManager->Civil_number, $request->password ? $request->password : 'عفوا لن يتم السماح لك بدخول السيستم', $newManager->email);
                }
            }
        } else {
            $Manager = User::find($manager);
            if ($request->password) {
                $Manager->sector = $sector->id;
                $Manager->flag = 'user';
                $Manager->rule_id = $request->rule;
                $Manager->password = Hash::make($request->password);
                $Manager->save();
                Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $Manager->Civil_number, $request->password ? $request->password : 'عفوا لن يتم السماح لك بدخول السيستم', $Manager->email);
            }
        }

        // Handle employee Civil_number updates
        $currentEmployees = User::where('sector', $sector->id)
            ->where('department_id', null)->whereNot('id', $manager)
            ->pluck('file_number')
            ->toArray();

        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = array_filter(explode(',,', $Civil_numbers));

        $employeesToRemove = array_diff($currentEmployees, $Civil_numbers);
        $employeesToAdd = array_diff($Civil_numbers, $currentEmployees);

        if (!empty($employeesToRemove)) {
            User::whereIn('file_number', $employeesToRemove)->update(['sector' => null, 'department_id' => null]);
        }

        foreach ($employeesToAdd as $Civil_number) {
            $number = trim($Civil_number);

            $employee = User::where('file_number', $number)->first();
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
