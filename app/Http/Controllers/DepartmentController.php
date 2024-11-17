<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\departements;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\DataTables\DepartmentDataTable;
use Illuminate\Support\Facades\Validator;
use App\DataTables\subDepartmentsDataTable;
use App\Http\Requests\StoreDepartmentRequest;
use App\Models\ReservationAllowance;
use App\Models\Rule;
use App\Models\Sector;
use Carbon\Carbon;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Hash;

class DepartmentController extends Controller
{
    public function index($id)
    {
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
            // dd('d');
            $departments = departements::all();
        } elseif (Auth::user()->rule->id == 4) {
            $departments = departements::where('sector_id', auth()->user()->sector);
        } elseif (Auth::user()->rule->id == 3) {
            $departments = departements::where('id', auth()->user()->department_id);
        }
        // Fetch the related sector information
        //$sectors = Sector::findOrFail($id);
        $sectors = get_by_md5_id($id, 'sectors');
        $departments = departements::where('sector_id', $sectors->id)->get();

        return view('departments.index', compact('departments', 'sectors'));
    }

    public function getAllowancedepart($amount, $departement_id)
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        $employees = ReservationAllowance::where('sector_id', $departement_id)
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

    public function getDepartment($id)
    {

        if (in_array(Auth::user()->rule->id, [1, 2, 4])) {

            $sectors = get_by_md5_id($id, 'sectors');

            $data = departements::where('parent_id', null)
                ->where('sector_id', $sectors->id)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $data = departements::where('parent_id', null)
                ->where('sector_id', $sectors->id)
                ->orderBy('id', 'desc')
                ->get();
        }
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-primary btn-sm">Edit</button>';
            })
            ->addColumn('reservation_allowance', function ($row) {
                switch ($row->reservation_allowance_type) {
                    case 1:
                        return 'حجز كلى';
                    case 2:
                        return 'حجز جزئى';
                    case 4:
                        return 'لا يوجد حجز';
                    default:
                        return 'حجز كلى و حجز جزئى';
                }
            })
            ->addColumn('subDepartment', function ($row) {
                return departements::where('parent_id', $row->id)->count();
            })
            ->addColumn('manager_name', function ($row) {
                return $row->manager ? $row->manager->name : 'لايوجد مدير للأداره';
            })
            ->addColumn('login_info', function ($row) {
                // Retrieve the manager (if exists)
                $LoginInfo = User::where('id', $row->manger)->first();

                // If there's no manager assigned
                if (!$LoginInfo) {
                    return 'لا يوجد مدير';
                }

                // If the manager exists and is flagged as 'employee'
                if ($LoginInfo->flag == 'employee') {
                    return 'لا يسمح له بالدخول';
                }

                // If the manager is a user (not an employee)
                $is_allow = $LoginInfo->file_number; // Display file number
                $p = 'اسم المستخدم :' . $is_allow . '<br>';
                $p .= 'اخر تسجيل دخول: ' . $LoginInfo->last_login;

                return $p;
            })

            ->addColumn('num_managers', function ($row) {
                return User::where('department_id', $row->id)
                    ->count();
            })
            ->addColumn('num_subdepartment_managers', function ($row) {
                $subdepartment_ids = departements::where('parent_id', $row->id)->pluck('id');
                return User::whereIn('department_id', $subdepartment_ids)
                    ->count();
            })
            ->rawColumns(['action', 'login_info'])
            ->make(true);
    }
    public function getManagerDetails($id)
    {
        // Fetch manager data from the database
        // $user = User::where('Civil_number', $id)->first();
        $user = User::where('file_number', $id)->first();
        if (!$user) {
            return response()->json(['error' => 'عفوا هذا المستخدم غير موجود'], 404);
        }

        // Check if the user is a sector manager
        $isSectorManager = Sector::where('manager', $user->id)->exists();

        // Prevent sector managers from being transferred or added
        if ($isSectorManager) {
            return response()->json(['error' => 'لا يمكن تعيين مدير قطاع كمدير أو موظف.'], 403);
        }

        // Check if the user is already assigned to a department
        if ($user->department_id) {
            $currentDepartment = Departements::find($user->department_id);
            $currentSector = $currentDepartment ? $currentDepartment->sector_id : null;

            // If the user is in a department in the same sector
            if ($currentSector == request()->get('sector_id')) {
                return response()->json([
                    'warning' => 'هذا المستخدم موجود بالفعل في إدارة أخرى في نفس القطاع. هل تريد نقله إلى هذه الإدارة؟',
                    'transfer' => true,
                    'rank' => $user->grade_id ? $user->grade->name : 'لا يوجد رتبه',
                    'seniority' => $user->joining_date ? Carbon::parse($user->joining_date)->diffInYears(Carbon::now()) : 'لا يوجد بيانات أقدميه',
                    'job_title' => $user->job_title ?? 'لا يوجد مسمى وظيفى',
                    'name' => $user->name,
                    'phone' => $user->phone ?? 'لا يوجد رقم هاتف',
                    'email' => $user->email ?? 'لا يوجد بريد الكتروني',
                    'isEmployee' => $user->flag == 'employee' ? true : false,
                ]);
            }

            // If the user is in a department in a different sector
            if ($currentSector !== request()->get('sector_id')) {
                return response()->json([
                    'warning' => 'هذا المستخدم موجود بالفعل في قطاع آخر. هل تريد نقله إلى هذا القطاع وهذه الإدارة؟',
                    'transfer' => true,
                    'rank' => $user->grade_id ? $user->grade->name : 'لا يوجد رتبه',
                    'seniority' => $user->joining_date ? Carbon::parse($user->joining_date)->diffInYears(Carbon::now()) : 'لا يوجد بيانات أقدميه',
                    'job_title' => $user->job_title ?? 'لا يوجد مسمى وظيفى',
                    'name' => $user->name,
                    'phone' => $user->phone ?? 'لا يوجد رقم هاتف',
                    'email' => $user->email ?? 'لا يوجد بريد الكتروني',
                    'isEmployee' => $user->flag == 'employee' ? true : false,
                ]);
            }
        }

        // If the user is not in any department or sector, return their details
        $joiningDate = $user->joining_date ? Carbon::parse($user->joining_date) : Carbon::parse($user->created_at);
        $today = Carbon::now();
        $yearsOfService = $joiningDate->diffInYears($today);

        // Check if the user is an employee (flag 'employee' means employee)
        $isEmployee = $user->flag == 'employee';

        return response()->json([
            'rank' => $user->grade_id ? $user->grade->name : 'لا يوجد رتبه',
            'job_title' => $user->job_title ?? 'لا يوجد مسمى وظيفى',
            'seniority' => $yearsOfService,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'isEmployee' => $isEmployee,
            'transfer' => false  // No transfer needed if they're not in any department or sector
        ], 200);
    }



    public function index_1($id)
    {

        $users = User::where('flag', 'employee')->where('department_id', NULL)->get();
        // $parentDepartment = get_by_md5_id($id, 'departements');

        $departments = departements::where('parent_id', $id)->get();
        $parentDepartment = departements::findOrFail($id);

        $breadcrumbs = $this->getDepartmentBreadcrumbs($parentDepartment);

        $sectors = Sector::findOrFail($parentDepartment->sector_id);

        return view('sub_departments.index', compact('users', 'departments', 'sectors', 'parentDepartment', 'breadcrumbs'));
    }

    public function getDepartmentBreadcrumbs($department)
    {
        $breadcrumbs = [];

        while ($department) {
            $breadcrumbs[] = $department;
            $department = departements::find($department->parent_id);
        }
        return array_reverse($breadcrumbs);
    }

    public function getSub_Department(Request $request, $id)
    {
        // $parentDepartment = get_by_md5_id($id, 'departments');

        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
            $data = departements::where('parent_id', $request->id)
                ->withCount('iotelegrams', 'outgoings', 'children')
                ->with(['children'])
                ->orderBy('id', 'desc')->get();
        } else {
            $data = departements::where('parent_id', $request->id)
                ->withCount('iotelegrams', 'outgoings', 'children')
                ->where(function ($query) {
                    $query->where('id', Auth::user()->department_id)
                        ->orWhere('parent_id', Auth::user()->department_id);
                })
                ->with(['children'])
                ->orderBy('id', 'desc')->get();
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-primary btn-sm">Edit</button>';
            })
            ->addColumn('reservation_allowance', function ($row) {
                return match ((int) $row->reservation_allowance_type) {
                    1 => 'حجز كلى',
                    2 => 'حجز جزئى',
                    4 => 'لا يوجد حجز',
                    3 => 'حجز كلى و حجز جزئى',
                    default => 'غير معروف',
                };
            })
            ->addColumn('subDepartment', function ($row) {
                $sub = departements::where('parent_id', $row->id)->count();
                return $sub;
            })
            ->addColumn('manager_name', function ($row) {
                return $row->manager ? $row->manager->name : 'لايوجد مدير للأداره';
            })
            ->addColumn('login_info', function ($row) {
                // Retrieve the manager (if exists)
                $LoginInfo = User::where('id', $row->manger)->first();

                // If there's no manager assigned
                if (!$LoginInfo) {
                    return 'لا يوجد مدير';
                }

                // If the manager exists and is flagged as 'employee'
                if ($LoginInfo->flag == 'employee') {
                    return 'لا يسمح له بالدخول';
                }

                // If the manager is a user (not an employee)
                $is_allow = $LoginInfo->file_number; // Display file number
                $p = 'اسم المستخدم :' . $is_allow . '<br>';
                $p .= 'اخر تسجيل دخول: ' . $LoginInfo->last_login;

                return $p;
            })

            ->addColumn('num_managers', function ($row) {
                return User::where('department_id', $row->id)
                    ->count();
            })
            ->addColumn('num_subdepartment_managers', function ($row) {
                $subdepartment_ids = departements::where('parent_id', $row->id)->pluck('id');
                return User::whereIn('department_id', $subdepartment_ids)
                    ->count();
            })

            ->rawColumns(['action', 'subDepartment', 'login_info'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        // $sectors = get_by_md5_id($id, 'sectors');
        $sectors = Sector::findOrFail($id);
        $managers = User::where('id', '!=', auth()->user()->id)
            ->whereNot('id', $sectors->manager)
            ->where(function ($query) use ($id) {
                $query->where('sector', $id)
                    ->orWhereNull('sector');
            })
            ->whereNull('department_id')
            ->get();

        $rules = Rule::where('id', 3)->get();

        return view('departments.create', compact('sectors', 'managers', 'rules'));
    }


    public function create_1($id)
    {
        $department = departements::findOrFail($id);

        // $department = get_by_md5_id($id, 'departments');
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
            $employees = User::where(function ($query) use ($id) {
                $query->where('department_id', $id)
                    ->orWhere('department_id', null);
            })
                ->where('flag', 'employee')
                ->whereNot('id', $department->manager)
                ->whereNot('id', auth()->user()->id)
                ->get();
            $managers = User::where('rule_id', 3)->get();
        } else {
            $employees = User::where(function ($query) use ($id) {
                $query->where('department_id', $id);
            })
                ->where('flag', 'employee')
                ->whereNot('id', $department->manager)
                ->whereNot('id', auth()->user()->id)
                ->get();
            $managers = User::where('department_id', $id)->whereNot('id', auth()->user()->id)->get();
        }
        return view('sub_departments.create', compact('department', 'employees', 'managers'));
    }

    public function getEmployeesByDepartment($departmentId)
    {
        // $currentEmployees = $department->employees()->pluck('id')->toArray();

        try {
            $employees = User::where('department_id', $departmentId)->get();
            return response()->json($employees);
        } catch (\Exception $e) {
            Log::error('Error fetching employees: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching employees'], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            // 'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'nullable|numeric|min:0|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }


        // Process file Numbers for employees
        $file_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->file_number);
        $file_numbers = array_filter(explode(',', $file_numbers)); // Ensure it's an array of valid numbers

        // Handle reservation allowance type
        $part = $request->input('part');
        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3; // Both '1' and '2' selected
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1; // Only '1' selected
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2; // Only '2' selected
        } elseif (in_array('3', $part)) {
            $reservation_allowance_type = 4; // Only '3' selected
        }

        // Retrieve the user by file_number and set the manager
        // $manager = $request->mangered ? User::where('Civil_number', $request->mangered)->first() : null;
        $manager = $request->mangered ? User::where('file_number', $request->mangered)->first() : null;

        // if ($manager) {
            // Create a new department
            $departements = new Departements();
            $departements->name = $request->name;
            $departements->manger = $manager? $manager->id : null; // Assign the user's ID as manager
            $departements->sector_id = $request->sector;
            $departements->description = $request->description;
            $departements->reservation_allowance_amount = $request->budget == null ? 0.00 : $request->budget;
            $departements->reservation_allowance_type = $reservation_allowance_type;
            $departements->created_by = Auth::user()->id;
            $departements->save();
            saveHistory($departements->reservation_allowance_amount, $departements->sector_id, $departements->id);

            if ($manager){
            // Handle manager assignment
            if ($manager->department_id != $departements->id || $manager->department_id != null) {
                $old_department = Departements::find($manager->department_id);

                if ($old_department) {
                    $old_department->manger = null;
                    $old_department->save();
                }
            }

            if ($manager->sector != $departements->sector_id || $manager->sector != null) {
                $old_sector = Sector::find($manager->sector);

                if ($old_sector) {
                    $old_sector->manager = null;
                    $old_sector->save();
                }
            }

            $manager->sector = $request->sector;
            $manager->department_id = $departements->id;
            if ($request->password) {
                $manager->flag = 'user';
                $manager->rule_id = $request->rule;
                $manager->password = Hash::make($request->password);
            }
            $manager->save();

            // Send email to new manager
            if ($manager->email) {
                // Send email to the new manager
                Sendmail(
                    'مدير ادارة',
                    'تم أضافتك كمدير ادارة',
                    $manager->file_number,
                    $request->password ? $request->password : null,
                    $manager->email
                );
            } else {
                return redirect()->route('departments.index', ['id' => $request->sector]);
            }
        }
        // } else {
        //     return redirect()->back()->withErrors('هذا المدير غير موجود')->withInput();
        // }

        // Handle employee assignment
        $failed_file_numbers = [];
        foreach ($file_numbers as $file_number) { //file_number
            //  $employee = User::where('Civil_number', $Civil_number)->first();
            $employee = User::where('file_number', $file_number)->first();
            if ($employee) {
                if ($employee->department_id && !$request->has('confirm_transfer')) {
                    $failed_file_numbers[] = $file_number; // Add to failed list if transfer not confirmed
                } else {
                    $employee->sector = $request->sector;
                    $employee->department_id = $departements->id;
                    $employee->save();
                }
            }
        }

        // Prepare success message
        $message = 'تم أضافه ادارة جديدة';
        if (count($failed_file_numbers) > 0) {
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم تأكيد النقل أو عدم العثور على ارقام الملفات: ' . implode(', ', $failed_file_numbers);
        }

        return redirect()->route('departments.index', ['id' => $request->sector])->with('message', $message);
    }

    public function store_1(Request $request)
    {
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            // 'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Validate the input fields
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'nullable|numeric|min:0|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $part = $request->input('part');
        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3; // Both '1' and '2' selected
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1; // Only '1' selected
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2; // Only '2' selected
        } elseif (in_array('3', $part)) {
            $reservation_allowance_type = 4; // Only '3' selected
        }        $file_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->file_number);
        $file_numbers = array_filter(explode(',', $file_numbers)); // Ensure it's an array of valid numbers
        $manager = $request->mangered ? User::where('file_number', $request->mangered)->first() : null;

        // Create a new sub-department
        $departements = new Departements();
        $departements->name = $request->name;
        $departements->sector_id = $request->sector;
        $departements->parent_id = $request->parent;
        $departements->description = $request->description;
        $departements->reservation_allowance_amount = $request->budget == null ? 0.00 : $request->budget;
        $departements->reservation_allowance_type = $reservation_allowance_type;
        $departements->created_by = Auth::user()->id;

        if ($manager) {
            // Assign the manager and handle the previous department/sector association
            $departements->manger = $manager->id;

            $old_department = Departements::find($manager->department_id);
            if ($old_department) {
                $old_department->manger = null;
                $old_department->save();
            }

            $old_sector = Sector::find($manager->sector);
            if ($old_sector) {
                $old_sector->manager = null;
                $old_sector->save();
            }

            $manager->sector = $request->sector;
            $manager->department_id = $departements->id;
            if ($request->password) {
                $manager->flag = 'user';
                $manager->rule_id = $request->rule;
                $manager->password = Hash::make($request->password);
            }
            $manager->save();
        }

        $departements->save();
        saveHistory($departements->reservation_allowance_amount, $departements->sector_id, $departements->id);

        // } else {
        //     return redirect()->back()->withErrors('هذا المدير غير موجود')->withInput();
        // }

        // Handle employee assignment
        $failed_file_numbers = [];
        foreach ($file_numbers as $file_number) { //file_number
            // $employee = User::where('Civil_number', $Civil_number)->first();
            $employee = User::where('file_number', $file_number)->first();

            if ($employee) {
                if ($employee->department_id && !$request->has('confirm_transfer')) {
                    $failed_file_numbers[] = $file_number; // Add to failed list if transfer not confirmed
                } else {
                    $employee->sector = $request->sector;
                    $employee->department_id = $departements->id;
                    $employee->save();
                }
            }
        }

        // Prepare success message
        $message = 'تم أضافه ادارة فرعية جديدة';
        if (count($failed_file_numbers) > 0) {
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم تأكيد النقل أو عدم العثور على ارقام الملفات: ' . implode(', ', $failed_file_numbers);
        }

        return redirect()->route('sub_departments.index', ['id' => $request->parent])->with('message', $message);
    }
    public function show($id)
    {
        // $department = get_by_md5_id_relation($id, 'sectors', array('manager', 'managerAssistant', 'children', 'parent'));
        $department = get_by_md5_id($id, 'departements');
        $department = departements::with(['manager', 'managerAssistant', 'children', 'parent'])->findOrFail($department->id);
        return view('departments.show', compact('department'));
    }

    //public function edit(departements $department)
    public function edit($id)
    {     
        $department = get_by_md5_id($id, 'departements');   
        $department = departements::findOrFail($department->id);   
        $id = $department->sector_id;
        $managers = User::where('id', '!=', auth()->user()->id)
            ->where(function ($query) use ($id) {
                $query->where('sector', $id)
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNull('sector');
                    });
            })
            // Ensure all users do not have a department
            ->get();
        $employees =  User::Where('department_id', $department->id)->whereNot('id', $department->manager)->get();

        $rules = Rule::where('id', 3)->get();

        return view('departments.edit', compact('department', 'managers', 'rules', 'employees'));
        // dd($employee);
        // return view('departments.edit', compact('department', 'users', 'employee'));
    }
    public function edit_1(departements $department)
    {
        // $department = get_by_md5_id_relation($department->parent_id, 'departments', array('sectors'));

        $sect = departements::with(['sectors'])->findOrFail($department->parent_id);
        if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {

            $employees = User::where(function ($query) use ($department) {
                $query->where('department_id', $department->id)
                    ->orWhere('department_id', null);
            })
                ->where('flag', 'employee')
                ->whereNot('id', $department->manager)
                ->whereNot('id', auth()->user()->id)
                ->get();
            $managers = User::where('department_id', 1)->orWhere('id', $department->manger)->orWhere('department_id', null)->where('rule_id', 3)->get();
        } else {
            $employees = User::where('flag', 'employee')->where('department_id', $department->id)->whereNot('id', $department->manager)->get();
            $managers = User::where('department_id', 1)->get();
        }
        $rules = Rule::where('id', 3)->get();

        return view('sub_departments.edit', compact('department', 'managers', 'employees', 'sect','rules'));
    }

    /**
     * Update the specified resource in storage.
     */
    //public function update(Request $request, departements $department)
    public function update(Request $request, $id)
    {
        $department = get_by_md5_id($id, 'departements');   
        $department = departements::findOrFail($department->id);   
        // Add a log or debugging output
        Log::info('Starting department update for department ID: ' . $department->id);

        // Define validation rules and messages
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'nullable|numeric|min:0|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $sectors_details = Sector::where('id', $request->sector)->first();

        $allowance = $this->getAllowancedepart($request->budget, $department->id);

        if (!$allowance->original['is_allow']) {
            $validator->errors()->add('budget', 'قيمه الميزانيه لا تتوافق، يرجى ادخال قيمه اكبر من ' . $allowance->original['total']);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve the old manager before updating
        $oldManager = $department->manger; //file_number
        // $manager = $request->mangered ? User::where('Civil_number', $request->mangered)->value('id') : null;
        $manager = $request->mangered ? User::where('file_number', $request->mangered)->value('id') : null;


        // Handle reservation allowance type
        $part = $request->input('part');
        $reservation_allowance_type = null;
        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3; // Both '1' and '2' selected
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1; // Only '1' selected
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2; // Only '2' selected
        } elseif (in_array('3', $part)) {
            $reservation_allowance_type = 4; // Only '3' selected
        }

        // Handle updating department details
        $department->name = $request->name;
        $department->sector_id = $request->sector;
        $department->description = $request->description;
        $department->manger = $manager;
        $department->reservation_allowance_type = $reservation_allowance_type;
        $department->reservation_allowance_amount = $request->budget == null ? 0.00 : $request->budget;
        $department->created_by = Auth::user()->id;
        $department->save();
        saveHistory($department->reservation_allowance_amount, $department->sector_id, $department->id);

        // Handle old and new manager updates
        if ($oldManager != $manager) {
            if ($oldManager) {
                $oldManagerUser = User::find($oldManager);
                if ($oldManagerUser) {
                    $oldManagerUser->sector = null;
                    $oldManagerUser->department_id = null;
                    $oldManagerUser->flag = 'employee';
                    $oldManagerUser->password = null;
                    $oldManagerUser->save();
                }
            }

            if ($manager) {
                $newManager = User::find($manager);
                if ($newManager->department_id != $department->id || $newManager->sector != null || $newManager->department_id != null) {
                    $old_department = departements::find($newManager->department_id);
                    if ($old_department) {
                        $old_department->manger = null;
                        $newManager->sector = $request->sector;
                        $old_department->save();
                    }
                }
                if ($newManager) {
                    $newManager->department_id = $department->id;
                    $newManager->sector = $request->sector;

                    if ($request->password) {
                        $newManager->flag = 'user';
                        $newManager->rule_id = $request->rule;
                        $newManager->password = Hash::make($request->password);
                    }
                    $newManager->save();

                    if ($newManager->email) {
                        // Send email to the new manager
                        Sendmail(
                            'مدير ادارة', // Subject
                            'تم أضافتك كمدير ادارة', // Email body
                            $newManager->file_number,
                            $request->password ? $request->password : null,
                            $newManager->email
                        );
                    } else {
                        return redirect()->route('departments.index', ['id' => $sectors_details->hash_id]);
                    }
                }
            }


        } else {
            $sector = Sector::find($request->id);
            $Manager = User::find($manager);
            if ($request->password) {
                $Manager->sector = $sector->id;
                $Manager->flag = 'user';
                $Manager->rule_id = $request->rule;
                $Manager->password = Hash::make($request->password);
                $Manager->save();
                if ($Manager->email) {
                    // Send email to the new manager
                    Sendmail('مدير ادارة', ' تم أضافتك كمدير ادارة' . $request->name, $Manager->file_number, $request->password ? $request->password : null, $Manager->email);
                } else {
                    return redirect()->route('departments.index', ['id' => $sectors_details->hash_id]);
                }
            }
        }

        
        // Handle employee updates
        $currentEmployees = User::where('sector', $request->sector)
            ->where('department_id', null)
            ->pluck('file_number')
            ->toArray();

        $file_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->file_number);
        $file_numbers = array_filter(explode(',', $file_numbers)); // Convert to array of file Numbers

        $employeesToRemove = array_diff($currentEmployees, $file_numbers);
        $employeesToAdd = array_diff($file_numbers, $currentEmployees);

        /*   if (!empty($employeesToRemove)) {//file_number
            User::whereIn('Civil_number', $employeesToRemove)
                ->update(['sector' => null, 'department_id' => null]);
        } */
        if (!empty($employeesToRemove)) { //file_number
            User::whereIn('file_number', $employeesToRemove)
                ->update(['sector' => null, 'department_id' => null]);
        }

        // Add new employees to the department
        foreach ($employeesToAdd as $file_number) {
            //  $employee = User::where('Civil_number', $Civil_number)->first();

            $employee = User::where('file_number', $file_number)->first();
            if ($employee && $employee->grade_id != null) {
                $employee->sector = $request->sector;
                $employee->department_id = $department->id;
                $employee->save();
            }
        }

        // Redirect after successful update
        //return redirect()->route('departments.index', ['id' => $request->sector])
        return redirect()->route('departments.index', $sectors_details->hash_id)->with('success', 'Department updated successfully.');
    }




    public function update_1(Request $request, departements $department)
    {
        // Log the update process for sub-departments
        Log::info('Starting sub-department update for department ID: ' . $department->id);

        // Validation rules and error messages
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            // 'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'nullable|numeric|min:0|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $allowance = $this->getAllowancedepart($request->budget, $department->id);
        if (!$allowance->original['is_allow']) {
            $validator->errors()->add('budget', 'قيمه الميزانيه لا تتوافق، يرجى ادخال قيمه اكبر من ' . $allowance->original['total']);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve old manager before updating
        $oldManager = $department->manger; //file_number
        // $manager = $request->mangered ? User::where('Civil_number', $request->mangered)->value('id') : null;
        $manager = $request->mangered ? User::where('file_number', $request->mangered)->value('id') : null;
        // Handle reservation allowance type
        $part = $request->input('part');
        $reservation_allowance_type = null;
        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3; // Both '1' and '2' selected
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1; // Only '1' selected
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2; // Only '2' selected
        } elseif (in_array('3', $part)) {
            $reservation_allowance_type = 4; // Only '2' selected
        }

        // Handle updating sub-department details
        $department->name = $request->name;
        $department->sector_id = $request->sector;
        $department->description = $request->description;
        $department->manger = $manager;
        $department->reservation_allowance_type = $reservation_allowance_type;
        $department->reservation_allowance_amount = $request->budget == null ? 0.00 : $request->budget;
        $department->created_by = Auth::user()->id;
        $department->save();
        saveHistory($department->reservation_allowance_amount, $department->sector_id, $department->id);

        // Handle old and new manager updates for sub-department
        if ($oldManager != $manager) {
            if ($oldManager) {
                $oldManagerUser = User::find($oldManager);
                if ($oldManagerUser) {
                    $oldManagerUser->department_id = null;
                    $oldManagerUser->sector = null;
                    $oldManagerUser->flag = 'employee';
                    $oldManagerUser->save();
                }
            }

            if ($manager) {
                $newManager = User::find($manager);
                if ($newManager->department_id != $department->id || $newManager->sector != null || $newManager->department_id != null) {
                    $old_department = departements::find($newManager->department_id);
                    if ($old_department) {
                        $old_department->manger = null;
                        $newManager->sector = $request->sector_id;
                        $old_department->save();
                    }
                }
                if ($newManager) {
                    $newManager->department_id = $department->id;
                    $newManager->sector = $request->sector_id;

                    if ($request->password) {
                        $newManager->flag = 'user';
                        $newManager->rule_id = $request->rule;
                        $newManager->password = Hash::make($request->password);
                    }
                    $newManager->save();
                    if ($newManager->email) {
                        // Send email notification to the new manager
                        Sendmail(
                            'مدير ادارة فرعية', // Subject
                            'تم أضافتك كمدير ادارة فرعية', // Email body
                            $newManager->file_number,
                            $request->password ? $request->password : null,
                            $newManager->email
                        );
                    } else {
                        return redirect()->route('sub_departments.index', ['id' => $request->sector]);
                    }
                }
            }
        } else {
            // If manager is not changed but password is updated, handle accordingly
            $Manager = User::find($manager);
            if ($request->password) {
                $Manager->sector = $department->sector_id;
                $Manager->flag = 'user';
                $Manager->rule_id = $request->rule;
                $Manager->password = Hash::make($request->password);
                $Manager->save();
                if ($manager->email) {
                    Sendmail('مدير ادارة فرعية', 'تم أضافتك كمدير ادارة فرعية ' . $request->name, $Manager->file_number, $request->password ? $request->password : null, $Manager->email);
                } else {
                    return redirect()->route('sub_departments.index', ['id' => $request->sector]);
                }
            }
        }

        // Handle employee updates in the sub-department
        $file_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->file_number);
        $file_numbers = array_filter(explode(',', $file_numbers)); // Convert to array of file Numbers
        //file_number
        // $currentEmployees = User::where('department_id', $department->id)->pluck('Civil_number')->toArray();
        $currentEmployees = User::where('department_id', $department->id)->pluck('file_number')->toArray();

        $employeesToRemove = array_diff($currentEmployees, $file_numbers);
        $employeesToAdd = array_diff($file_numbers, $currentEmployees);

        // Remove employees that are no longer in this sub-department
        if (!empty($employeesToRemove)) {
            //  User::whereIn('Civil_number', $employeesToRemove)->update(['department_id' => null, 'sector' => null]);
            User::whereIn('file_number', $employeesToRemove)->update(['department_id' => null, 'sector' => null]);
        }

        // Add new employees to the sub-department
        foreach ($employeesToAdd as $file_number) {
            //  $employee = User::where('Civil_number', $Civil_number)->first();
            $employee = User::where('file_number', $file_number)->first();
            if ($employee) {
                $employee->department_id = $department->id;
                $employee->sector = $department->sector_id;
                $employee->save();
            }
        }

        return redirect()->route('sub_departments.index', ['id' => $department->parent_id])
            ->with('success', 'Sub-department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(departements $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
        // return response()->json(null, 204);
    }
}
