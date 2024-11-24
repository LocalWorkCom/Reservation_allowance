<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\departements;
use App\Models\Government;
use App\Models\history_allawonce;
use App\Models\ReservationAllowance;
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
use Hashids\Hashids;
use Illuminate\Support\Facades\Log;

class sectorsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function getManagerSectorDetails($id, $sector)
    {
        // Fetch manager data using the file_number
        $manager = User::where('file_number', $id)->first();

        // Handle if no manager is found
        if (!$manager) {
            return response()->json(['error' => 'عفوا هذا المستخدم غير موجود'], 405);
        }

        // Handle the case where $sector is 'null'
        if ($sector === 'null') {
            $sector = null;
        }

        // Check for the 'skipDepartmentCheck' flag to perform department/sector validation
        $isDepartmentCheck = request()->has('skipDepartmentCheck') && request()->get('skipDepartmentCheck') === 'true';
        if ($isDepartmentCheck && ($manager->department_id != null || $manager->sector != null || ($sector != $manager->sector && $manager->sector != null))) {
            return response()->json([
                'error' => 'هذا المستخدم موجود فى قطاع مسبقا . هل تريد نقله ?'
            ], 404);
        }

        // Calculate seniority (years of service)
        $joiningDate = $manager->joining_date ? Carbon::parse($manager->joining_date) : Carbon::parse($manager->created_at);
        $today = Carbon::now();
        $yearsOfService = $joiningDate->diffInYears($today) ?? 'لا يوجد بيانات أقدميه';

        // Check if the manager is an employee (based on the 'employee' flag)
        $isEmployee = $manager->flag === 'employee';

        // Return the manager data as JSON
        return response()->json([
            'rank' => $manager->grade_id ? $manager->grade->name : 'لا يوجد رتبه',
            'job_title' => $manager->job_title ?? 'لا يوجد مسمى وظيفى',

            'name' => $manager->name,
            'phone' => $manager->phone ?? 'لا يوجد رقم هاتف',
            'email' => $manager->email ?? 'لا يوجد بريد الكتروني',
            'isEmployee' => $isEmployee,
        ]);
    }
    public function getAllowance($amount, $sectorId)
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        $employees = ReservationAllowance::where('sector_id', $sectorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Calculate total amount for the specified sector and date range
        $totalAmount = $employees->sum('amount');
        $is_allow = $totalAmount < $amount;

        // Return total amount and is_allow status
        return response()->json([
            'total' => $totalAmount,
            'is_allow' => $is_allow
        ]);
    }




    public function index()
    {
        addUuidToTable('sectors');
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
                $edit_permission = '<a class="btn btn-sm" style="background-color: #F7AF15;" href=' . route('sectors.edit', $row->uuid) . '><i class="fa fa-edit"></i> تعديل</a>';
                $add_permission = '<a class="btn btn-sm" style="background-color: #bb5207;" href="' .  route('department.create', $row->uuid) . '"><i class="fa fa-plus"></i> أضافة أداره</a>';
                $reservationAllowence = '<a class="btn btn-sm" style="background-color: #1d88a1;" href=' . route('reservation_allowances.search_employee_new', 'sector_id=' . $row->hash_id) . '><i class="fa fa-plus"></i> اضافة بدل حجز جماعى</a>';
                $show_permission = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('sectors.show', $row->uuid) . '> <i class="fa fa-eye"></i>عرض</a>';
                // $addbadal_permission = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('sectors.show', $row->id) . '> <i class="fa fa-plus"></i>أضافه بدل</a>';

                return $show_permission . ' ' . $edit_permission . '' . $add_permission; //$reservationAllowence;
            })
            ->addColumn('manager_name', function ($row) {
                // Check if manager exists before accessing its attributes
                $manager = User::find($row->manager);
                // $manager = User::find($row->manager);
                if ($manager) {
                    // Check the flag to determine if the manager is an employee
                    $is_allow = $manager->flag == 'employee' ? 'لا يسمح بالدخول' : 'يسمح بالدخول';
                    // Return the manager's name along with the access permission status
                    return $manager->name . ' (' . $is_allow . ')';
                }
                return 'لا يوجد مدير';
            }) //login_info
            ->addColumn('login_info', function ($row) {
                // Check if manager exists before accessing its attributes
                $LoginInfo = User::find($row->manager);
                if ($LoginInfo) {
                    $is_allow = $LoginInfo->flag == 'employee' ? 'لا يسمح بالدخول' : $LoginInfo->file_number;
                    $p = 'اسم المستخدم :' . $is_allow . ' ــــــــــ ';
                    $p .= 'اخر تسجيل دخول ' . $LoginInfo->last_login . '';
                    return $p;
                }
                return 'لا توجد بيانات دخول ';
            })
            ->addColumn('departments', function ($row) {
                $num = departements::where('sector_id', $row->id)->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('departments.index', ['uuid' => $row->uuid]) . '> ' . $num . '</a>';
                return $btn;
            })
            ->addColumn('reservation_allowance_amount', function ($row) {
                return $row->reservation_allowance_amount == 0.00 ? 'ميزانيه مفتوحه' : $row->reservation_allowance_amount;
            })
            ->addColumn('reservation_allowance', function ($row) {
                if ($row->reservation_allowance_type == 1) {
                    return 'حجز كلى';
                } elseif ($row->reservation_allowance_type == 2) {
                    return 'حجز جزئى';
                } elseif ($row->reservation_allowance_type == 4) {
                    return 'لا يوجد بدل حجز';
                } else {
                    return 'حجز كلى و حجز جزئى';
                }
            })
            ->addColumn('employees', function ($row) {
                $emp_num = User::where('sector', $row->id)->where('department_id', null)->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373;" href=' . route('user.employees', ['sector_id' => $row->uuid, 'type' => 0, 'flag' => 'user']) . '> ' . $emp_num . '</a>';
                return $btn;
            })
            ->addColumn('employeesdep', function ($row) {
                $emp_num = User::where('sector', $row->id)->whereNotNull('department_id')->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373; padding-inline: 15p" href=' . route('user.employees', ['sector_id' => $row->uuid, 'type' => 1, 'flag' => 'user']) . '> ' . $emp_num . '</a>';
                return $btn;
            })
            ->rawColumns(['action', 'departments', 'employees', 'login_info', 'employeesdep'])
            ->make(true);
    }


    public function create()
    {
        $users = User::where('department_id', null)->where('sector', null)->get();
        $rules = Rule::where('name', 'sector manager')->get();
        return view('sectors.create', compact('users', 'rules'));
    }


    public function store(Request $request)
    {
        // Custom error messages for validation
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            // 'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            // 'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.00.',
            //'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'nullable|numeric',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        //dd($request->all());
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
        $reservation_allowance_type = match (true) {
            in_array('1', $part) && in_array('2', $part) => 3,
            in_array('1', $part) => 1,
            in_array('2', $part) => 2,
            in_array('3', $part) => 4,
            default => null, // Default to null if no match
        };


        // Create and save new sector
        $sector = new Sector();
        $sector->name = $request->name;
        $sector->reservation_allowance_type = $reservation_allowance_type;
        $sector->reservation_allowance_amount = $request->budget_type == 2 ? 00.00 : $request->budget;
        $sector->manager = $manager;
        $sector->created_by = Auth::id();
        $sector->updated_by = Auth::id();
        $sector->save();
        saveHistory($sector->reservation_allowance_amount, $sector->id, $request->department_id);

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

            $user->flag = 'user';
            $user->rule_id = 4;
            $user->email = $request->email;
            $user->password = Hash::make('123456');
            $user->save();
            if ($user->email && isValidEmail($manager->email)) {
                Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع ' . $request->name, $user->file_number, 123456, $user->email);
            }else {
                return redirect()->back()->withErrors(['email' => 'البريد الإلكتروني للمدير غير صالح.'])->withInput();
            }
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
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم العثور على الأرقام الملف أو عدم وجود درجة لهم: ' . implode(', ', $failed_civil_numbers);
        }

        // Redirect to sectors index with success message
        return redirect()->route('sectors.index')->with('message', $message);
    }


    /**
     * Display the specified resource.
     */
    public function show(Sector $sector)
    {
        $data = $sector;
        $manager = User::find($data->manager);
        $managerName = $manager->name ?? 'لا يوجد مدير';
        // $data = Sector::find($id);
        $users = User::where('department_id', null)->whereNot('id', $data->manager ?? null)->Where('sector', $data->id)->get();
        $departments = departements::where('sector_id', $data->id)->get();
        return view('sectors.showdetails', compact('data', 'managerName', 'users', 'departments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sector $sector)
    {
        // $data = Sector::findOrFail($id);
        $data = $sector;
        $users = User::where('department_id', null)->where('sector', null)->orWhere('sector', $data->id)->get();
        $employees =  User::where('department_id', null)->Where('sector', $data->id)->whereNot('id', $data->manager)->get();
        $rules = Rule::whereNotIn('id', [1, 2, 3])->get();
        $manager = User::find($data->manager);

        $fileNumber = $manager->file_number ?? null;
        return view('sectors.edit', [
            'data' => $data,
            'users' => $users,
            'employees' => $employees,
            'rules' => $rules,
            'fileNumber' => $fileNumber,
            'email' => $manager->email ?? null,
            'budget' => $data->reservation_allowance_amount


        ]);
    }


    public function update(Request $request, Sector $sector)
    {
        $sector = Sector::find($request->id);
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'nullable|numeric',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $allowance = $this->getAllowance($request->budget, $request->id);

        if (!$allowance->original['is_allow']) {
            $validator->errors()->add('budget',  '  قيمه الميزانيه لا تتوافق، يرجى ادخال قيمه اكبر من ' . $allowance->original['total'] . 'لوجود بدلات حجز اكبر من القيمه المدخله');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldManager = $sector->manager;
        $manager = $request->mangered ? User::where('file_number', $request->mangered)->value('id') : null;

        // If a new manager is provided but not found in the system
        if ($request->mangered && $manager == null) {
            return redirect()->back()->withErrors('رقم هويه المدير غير موجود')->withInput();
        }
        //dd($request->all());
        // Determine reservation_allowance_type based on 'part'
        $part = $request->input('part');
        $reservation_allowance_type = null;
        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3;
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1;
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2;
        } elseif (in_array('3', $part)) {
            $reservation_allowance_type = 4;
        }

        // Update sector details
        $sector->name = $request->name;
        $sector->reservation_allowance_type = $reservation_allowance_type;
        $sector->reservation_allowance_amount = $request->budget_type == 2 ? 00.00 : $request->budget;
        $sector->manager = $manager;
        $sector->updated_by = Auth::id();
        $sector->save();
        saveHistory($sector->reservation_allowance_amount, $sector->id, $request->department_id);

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
                // dd($manager);

                if ($newManager->sector != null && $newManager->sector != $sector->id) {
                    // Fetch the old sector that the new manager was responsible for
                    $old_sector = Sector::where('manager', $manager)->whereNot('id', $sector->id)->first();

                    // If the old sector exists, set its manager to null and save the changes
                    if ($old_sector) {
                        $old_sector->manager = null;  // Unassign the manager
                        $old_sector->save();  // Save the changes to the old sector
                    }
                }
                if ($newManager) {
                    $newManager->sector = $sector->id;
                    $newManager->department_id = null;

                    $newManager->flag = 'user';
                    $newManager->rule_id = 4;
                    $newManager->email = $request->email;
                    $newManager->password = Hash::make('123456');
                    $newManager->save();
                    if ($newManager->email && isValidEmail($manager->email))  {
                        Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $newManager->Civil_number, 123456, $newManager->email);
                    }else {
                        return redirect()->back()->withErrors(['email' => 'البريد الإلكتروني للمدير غير صالح.'])->withInput();
                    }
                }
            }
        } else {
            $Manager = User::find($manager);
            if ($request->password) {
                $Manager->sector = $sector->id;
                $Manager->flag = 'user';
                $Manager->rule_id = 4;
                $Manager->password = Hash::make('123456');
                $Manager->eamil = $request->email;

                $Manager->save();
                if ($Manager->email && isValidEmail($manager->email)) {
                    Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $Manager->Civil_number,  123456, $Manager->email);
                }else {
                    return redirect()->back()->withErrors(['email' => 'البريد الإلكتروني للمدير غير صالح.'])->withInput();
                }
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
        $failed_civil_numbers = [];

        foreach ($employeesToAdd as $Civil_number) {
            $number = trim($Civil_number);

            $employee = User::where('file_number', $number)->first();
            $is_manager = Sector::where('manager', $employee->id)->exists();
            if ($employee && $employee->grade_id != null && !$is_manager) {
                $employee->sector = $sector->id;
                $employee->save();
            } else {
                // Add Civil_number to the failed list if the employee is not found or has no grade_id
                $failed_civil_numbers[] = $Civil_number;
            }
        }

        // Prepare success message
        $message = 'تم تعديل القطاع ';
        // dd(count);
        // Append failed Civil numbers to the message, if any
        if (count($failed_civil_numbers) > 0) {
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم العثور على الأرقام الملف أو عدم وجود درجة لهم: ' . implode(', ', $failed_civil_numbers);
        }
        return redirect()->route('sectors.index')->with('message', $message);
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
