<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\departements;
use App\Models\ReservationAllowance;
use Illuminate\Validation\Rule;
use App\Models\Sector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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
            return response()->json(['error' => 'عفوا هذا المستخدم غير موجود'], 404);
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
            ], 405);
        }

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
        if ($employees) {
            $totalAmount = $employees->sum('amount');

            if ($totalAmount == 0) {
                $is_allow = true;
            } else {
                $is_allow = $totalAmount < $amount;
            }
        } else {
            $totalAmount = 0;
            $is_allow = true;
        }


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
            $btn = '
                <select class="form-select form-select-sm btn-action" onchange="handleAction(this.value, \'' . $row->uuid . '\')" aria-label="Actions" style="width: auto;">
                    <option value="" class="text-center" style="color: gray;" selected disabled>الخيارات</option>';

            if (Auth::user()->hasPermission('view Sector')) {
                $btn .= '<option value="show" class="text-center" data-url="' . route('sectors.show', $row->uuid) . '" style="color: #274373;">عرض</option>';
            }

            if (Auth::user()->hasPermission('edit Sector')) {
                $btn .= '<option value="edit" class="text-center" data-url="' . route('sectors.edit', $row->uuid) . '" style="color:#eb9526;">تعديل</option>';
            }

            if (Auth::user()->hasPermission('create departements')) {
                $btn .= '<option value="create-department" class="text-center" data-url="' . route('department.create', $row->uuid) . '" style="color:#c50c0c;">أضافة أداره</option>';
            }

            $btn .= '</select>';
            return $btn;
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
                $btn = '<a class="btn btn-sm" style="background-color: #274373;color: white;  padding-inline: 15px" href=' . route('departments.index', ['uuid' => $row->uuid]) . '> ' . $num . '</a>';
                return $btn;
            })
            ->addColumn('reservation_allowance_amount', function ($row) {
                return $row->reservation_allowance_amount == 0.00 ? 'ميزانيه غير محدده' : $row->reservation_allowance_amount . " د.ك";
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
                $emp_num = User::where('sector', $row->id)->where('flag', 'employee')->where('department_id', null)->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373;color: white; padding-inline: 15px" href=' . route('user.employees', ['id' => $row->uuid, 'type' => 'sector', 'status' => 'null', 'flag' => 'employee']) . '> ' . $emp_num . '</a>';
                return $btn;
            })
            ->addColumn('employeesdep', function ($row) {
                $emp_num = User::where('sector', $row->id)->where('flag', 'employee')->whereNotNull('department_id')->count();
                $btn = '<a class="btn btn-sm" style="background-color: #274373;color: white;  padding-inline: 15px" href=' . route('user.employees', ['id' => $row->uuid, 'type' => 'sector', 'status' => 'notnull', 'flag' => 'employee']) . '> ' . $emp_num . '</a>';

                return $btn;
            })
            ->rawColumns(['action', 'departments', 'employees', 'login_info', 'employeesdep'])
            ->make(true);
    }


    public function create()
    {
        $users = User::where('department_id', null)->where('sector', null)->get();
        return view('sectors.create', compact('users'));
    }


    public function store(Request $request)
    {
        // Custom error messages for validation
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
            'email.required' => 'الايميل مطلوب.',
            'email.unique' => 'الايميل مأخوذ مسبقا و يرجى أدخال أيميل أخر.',
            'budget_type.required' => 'يجب اختيار نوع الميزانيه.',
            'email.invalid_format' => 'البريد الإلكتروني للمدير غير صالح.', // Custom error message
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget_type' => 'required',
            'budget' => 'nullable|numeric',
            'part' => 'required',
            'email' => [
                'nullable', // Allow email to be null unless manager is set
                Rule::unique('users', 'email')->ignore($request->mangered, 'file_number'),
                function ($attribute, $value, $fail) {
                    // Check if email format is invalid
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('البريد الإلكتروني للمدير غير صالح.'); // Custom failure message
                    }
                },
            ],
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        // Process Civil_numbers input into an array
        $Civil_numbers = str_replace(["\r", "\r\n", "\n"], ',', $request->Civil_number);
        $Civil_numbers = explode(',', $Civil_numbers);

        // Find employees based on Civil_number (file_number)
        $employees = User::whereIn('file_number', $Civil_numbers)->pluck('id')->toArray();

        // Initialize manager variable
        $manager = null;

        if ($request->mangered) {
            // Find manager based on Civil Number
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
            default => null,
        };

        // Create and save new sector
        $sector = new Sector();
        $sector->name = $request->name;
        $sector->reservation_allowance_type = $reservation_allowance_type;
        $sector->reservation_allowance_amount = $request->budget_type == 2 ? 0.00 : $request->budget;
        $sector->manager = $manager;
        $sector->created_by = Auth::id();
        $sector->updated_by = Auth::id();
        $sector->save();

        // Save history
        saveHistory($sector->reservation_allowance_amount, $sector->id, $request->department_id);
        UpdateUserHistory($manager);
        addUserHistory($manager, null, $sector->id);

        if ($manager) {
            $user = User::find($manager);

            if (!$user) {
                return redirect()->back()->with('error', 'هذا المستخدم غير موجود');
            }

            if ($user->sector != $sector->id || $user->sector != null) {
                $old_sector = Sector::find($user->sector);

                if ($old_sector) {
                    $old_sector->manager = null;
                    $old_sector->save();
                    UpdateUserHistory($manager);
                    addUserHistory($manager, null, $sector->id);
                }
            }

            $user->sector = $sector->id;
            $user->department_id = null;
            $user->flag = 'user';
            $user->rule_id = 4;
            $user->email = $request->email;
            $user->password = Hash::make('123456');
            $user->save();

            if ($user->email && isValidEmail($user->email)) {
                Sendmail('مدير قطاع', 'تم أضافتك كمدير قطاع ' . $request->name, $user->file_number, 123456, $user->email);
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
                UpdateUserHistory($employee->id);
                addUserHistory($employee->id, null, $sector->id);
            } else {
                $failed_civil_numbers[] = $Civil_number;
            }
        }

        // Prepare success message
        $message = 'تم أضافه قطاع جديد';

        if (count($failed_civil_numbers) > 0) {
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم العثور على الأرقام الملف أو عدم وجود درجة لهم: ' . implode(', ', $failed_civil_numbers);
        }

        return redirect()->route('sectors.index')->with('message', $message);
    }



    /**
     * Display the specified resource.
     */
    public function show(Sector $sector)
    {
        $data = $sector;
        $manager = User::find($data->manager);
        $users = User::where('flag', 'employee')->where('department_id', null)->where('sector', $data->id)->get();
        $managerName = $manager->name ?? 'لا يوجد مدير';
        $departments = departements::where('sector_id', $data->id)->get();
        return view('sectors.showdetails', compact('data', 'managerName', 'departments', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sector $sector)
    {
        $data = $sector;
        $users = User::where('department_id', null)->where('sector', null)->orWhere('sector', $data->id)->get();
        $employees =  User::where('department_id', null)->Where('sector', $data->id)->whereNot('id', $data->manager)->get();
        $manager = User::find($data->manager);
        $fileNumber = $manager->file_number ?? null;
        return view('sectors.edit', [
            'data' => $data,
            'users' => $users,
            'employees' => $employees,
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
            'email.required' => 'الايميل مطلوب.',
            'email.unique' => 'الايميل مأخوذ مسبقا و يرجى أدخال أيميل أخر.',
            'budget_type.required' => 'يجب اختيار نوع الميزانيه.',
            'email.invalid_format' => 'البريد الإلكتروني للمدير غير صالح.', // Custom error message
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget_type' => 'required',
            'budget' => 'nullable|numeric',
            'part' => 'required',
            'email' => [
                'nullable', // Allow email to be null unless manager is set
                Rule::unique('users', 'email')->ignore($request->mangered, 'file_number'),
                function ($attribute, $value, $fail) {
                    // Check if email format is invalid
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('البريد الإلكتروني للمدير غير صالح.'); // Custom failure message
                    }
                },
            ],
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check allowance condition and add custom error if needed
        $allowance = $this->getAllowance($request->budget, $request->id);

        $allowanceData = json_decode($allowance->getContent(), true);  // Decode JSON response

        // Now you can check the 'is_allow' value as expected
        if ($allowanceData['is_allow'] === false) {
            $errorMessage = '  قيمه الميزانيه لا تتوافق، يرجى ادخال قيمه اكبر من ' . $allowance->original['total'] . ' لوجود بدلات حجز اكبر من القيمه المدخله';

            // Add the custom budget error to the validator's errors
            $validator->errors()->add('budget', $errorMessage);
            return redirect()->back()->withErrors($validator)->withInput();
        }
        // Continue with further logic if validation passes

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
        UpdateUserHistory($manager);
        addUserHistory($manager, null, $sector->id);
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
                    $department = departements::where('manger', $newManager->id)->first();
                    if ($department) {
                        $department->manger = null;
                        $department->save();
                    }
                    $sector_manager = Sector::where('manager', $newManager)->whereNot('id', $sector->id)->get();
                    if ($sector_manager->isNotEmpty()) {
                        $sector_manage = Sector::where('manager', $newManager)->whereNot('id', $sector->id)->first();
                        $sector_manage->manager = null;
                        $sector_manage->save();
                    }
                    $newManager->sector = $sector->id;
                    $newManager->department_id = null;
                    $newManager->flag = 'user';
                    $newManager->rule_id = 4;
                    $newManager->email = $request->email;
                    $newManager->password = Hash::make('123456');
                    $newManager->save();
                    if ($newManager->email && isValidEmail($newManager->email)) {
                        Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $newManager->Civil_number, 123456, $newManager->email);
                    }
                }
            }
        } else {
            $sector_manager = Sector::where('manager', $manager)->whereNot('id', $sector->id)->get();

            if ($sector_manager->isNotEmpty()) {
                $sector_manage = Sector::where('manager', $manager)->whereNot('id', $sector->id)->first();
                $sector_manage->manager = null;
                $sector_manage->save();
            }
            $Manager = User::find($manager);
            if ($request->password) {
                $Manager->sector = $sector->id;
                $Manager->flag = 'user';
                $Manager->rule_id = 4;
                $Manager->password = Hash::make('123456');
                $Manager->eamil = $request->email;
                $Manager->save();
                if ($Manager->email && isValidEmail($Manager->email)) {
                    Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $Manager->Civil_number,  123456, $Manager->email);
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
            if ($employee && $employee->grade_id != null) {
                $is_manager = Sector::where('manager', $employee->id)->exists();
                if (!$is_manager) {
                    $employee->sector = $sector->id;

                    $employee->department_id = null;
                    $employee->save();
                    UpdateUserHistory($employee->id);
                    addUserHistory($employee->id, null, $sector->id);
                }
            } else {
                // Add Civil_number to the failed list if the employee is not found or has no grade_id
                $failed_civil_numbers[] = $Civil_number;
            }
        }
        // Prepare success message
        $message = 'تم تعديل القطاع ';
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
