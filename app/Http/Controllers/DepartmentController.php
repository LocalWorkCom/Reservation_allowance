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
        $departments = departements::where('sector_id', $id)->get();

        // Fetch the related sector information
        $sectors = Sector::findOrFail($id);

        return view('departments.index', compact('departments', 'sectors'));
    }


    public function getDepartment($id)
    {


        if (in_array(Auth::user()->rule->id, [1, 2, 4])) {

            $data = departements::where('parent_id', null)
                ->where('sector_id', $id)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $data = departements::where('parent_id', null)
                ->where('sector_id', $id)
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
            ->addColumn('num_managers', function ($row) {
                return User::where('department_id', $row->id)
                    ->count();
            })
            ->addColumn('num_subdepartment_managers', function ($row) {
                $subdepartment_ids = departements::where('parent_id', $row->id)->pluck('id');
                return User::whereIn('department_id', $subdepartment_ids)
                    ->count();
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function getManagerDetails($id)
    {
        // Fetch manager data from the database
        $user = User::where('Civil_number', $id)->first();
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
                    'transfer' => true
                ]);
            }

            // If the user is in a department in a different sector
            if ($currentSector !== request()->get('sector_id')) {
                return response()->json([
                    'warning' => 'هذا المستخدم موجود بالفعل في قطاع آخر. هل تريد نقله إلى هذا القطاع وهذه الإدارة؟',
                    'transfer' => true
                ]);
            }
        }

        // If the user is not in any department or sector, return their details
        $joiningDate = $user->joining_date ? Carbon::parse($user->joining_date) : Carbon::parse($user->created_at);
        $today = Carbon::now();
        $yearsOfService = $joiningDate->diffInYears($today);

        // Check if the user is an employee (flag 'employee' means employee)
        $isEmployee = $user->flag == 'employee';

        // Return the manager/employee data in JSON format
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
        $departments = departements::where('parent_id', $id)->get();
        $parentDepartment = departements::findOrFail($id);

        $breadcrumbs = $this->getDepartmentBreadcrumbs($parentDepartment);

        $sectors = Sector::findOrFail($parentDepartment->sector_id);

        return view('sub_departments.index', compact('users', 'departments', 'sectors', 'parentDepartment', 'breadcrumbs'));
    }

    public function getDepartmentBreadcrumbs($department)
    {
        $breadcrumbs = [];

        // Keep fetching parent departments until we reach the top-most department (parent_id is null)
        while ($department) {
            // Add the current department to the breadcrumbs array
            $breadcrumbs[] = $department;

            // Fetch the parent department if it exists
            $department = departements::find($department->parent_id);
        }

        // Reverse the breadcrumbs to start from the top-most parent
        return array_reverse($breadcrumbs);
    }
    public function getSub_Department(Request $request, $id)
    {
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
                return match ($row->reservation_allowance_type) {
                    1 => 'حجز كلى',
                    2 => 'حجز جزئى',
                    default => 'حجز كلى و حجز جزئى',
                };
            })
            ->addColumn('subDepartment', function ($row) { // New column for departments count
                $sub = departements::where('parent_id', $row->id)->count();
                return $sub;
            })
            ->addColumn('manager_name', function ($row) {
                return $row->manager ? $row->manager->name : 'لايوجد مدير للأداره';
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

            ->rawColumns(['action', 'subDepartment'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {


        //create Main Administration
        $sectors = Sector::findOrFail($id);
        $managers = User::where('id', '!=', auth()->user()->id)
            ->whereNot('id', $sectors->manager)
            ->where(function ($query) use ($id) {
                $query->where('sector', $id)
                    ->orWhereNull('sector');
            })
            ->whereNull('department_id') // Ensure all users do not have a department
            ->get();

        $rules = Rule::where('id', 3)->get();

        return view('departments.create', compact('sectors', 'managers', 'rules'));
    }


    public function create_1($id)
    {
        $department = departements::findOrFail($id);
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
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'required|numeric|min:0|max:1000000',
            'part' => 'required',

        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = explode(',,', $Civil_numbers);

        // Find employees based on Civil_number
        $employees = User::whereIn('Civil_number', $Civil_numbers)->pluck('id')->toArray();

        // Initialize manager variable
        $manager = null;

        // Handle the case if `mangered` is provided
        if ($request->mangered) {
            $manager = User::where('Civil_number', $request->mangered)->first();

            // Check if the Civil Number belongs to any sector manager
            $isSectorManager = Sector::where('manager', $manager->id)->exists();
            if ($isSectorManager) {
                return redirect()->back()->withErrors('لا يمكن تعيين مدير قطاع كمدير أو موظف.')->withInput();
            }

            // Check if the manager is already in a department and confirm transfer if needed
            if ($manager->department_id && $request->has('confirm_transfer')) {
                // Update the department and sector if transfer is confirmed
                $manager->sector = $request->sector;
                $manager->department_id = null;  // Clear the current department if transferring
                $manager->save();
            } elseif ($manager->department_id) {
                return redirect()->back()->withErrors('هذا المستخدم موجود بالفعل في قطاع آخر.')->withInput();
            }
        }

        $part = $request->input('part');
        $reservation_allowance_type = null;

        if (in_array('1', $part) && in_array('2', $part)) {
            // If both '1' and '2' are present, save 3
            $reservation_allowance_type = 3;
        } elseif (in_array('1', $part)) {
            // If only '1' is present, save 1
            $reservation_allowance_type = 1;
        } elseif (in_array('2', $part)) {
            // If only '2' is present, save 2
            $reservation_allowance_type = 2;
        }
        $departements = new Departements();
        $departements->name = $request->name;
        $departements->manger = $request->manger;
        $departements->sector_id  = $request->sector;
        $departements->description = $request->description;
        $departements->reservation_allowance_amount = $request->budget;
        $departements->reservation_allowance_type = $reservation_allowance_type;
        $departements->created_by = Auth::user()->id;
        $departements->save();

        // Handle manager assignment and email notification
        if ($manager) {
            $user = User::find($manager->id);
            if ($user) {
                $user->sector = $request->sector;
                $user->department_id = $departements->id;
                if ($request->password) {
                    $user->flag = 'user';
                    $user->rule_id = $request->rule;
                    $user->password = Hash::make($request->password);
                }
                $user->save();

                // Send email notification
                Sendmail(
                    'مدير ادارة',
                    'تم أضافتك كمدير ادارة',
                    $user->Civil_number,
                    $request->password ? $request->password : null,
                    $user->email
                );
            } else {
                return redirect()->back()->with('error', 'هذا المستخدم غير موجود');
            }
        }

        /// Handle employee transfers
        $failed_civil_numbers = [];
        foreach ($Civil_numbers as $Civil_number) {
            $employee = User::where('Civil_number', $Civil_number)->first();
            if ($employee && $employee->grade_id !== null) {
                // Check if the employee is already assigned to a department
                if ($employee->department_id && $request->has('confirm_transfer')) {
                    // Transfer the employee to the new department if confirmed
                    $employee->sector = $request->sector;
                    $employee->department_id = $departements->id;
                    $employee->save();
                } elseif ($employee->department_id) {
                    $failed_civil_numbers[] = $Civil_number;
                }
            }
        }

        // Prepare success message
        $message = 'تم أضافه ادارة جديد';

        // Append failed Civil numbers to the message, if any
        if (count($failed_civil_numbers) > 0) {
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم العثور على الأرقام المدنية أو عدم وجود درجة لهم: ' . implode(', ', $failed_civil_numbers);
        }
        return redirect()->route('departments.index', ['id' => $request->sector])->with('message', $message);
    }


    public function store_1(Request $request)
    {
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',
            'part.required' => 'نوع بدل الحجز مطلوب.',
        ];

        // Validate the input fields
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'required|numeric|min:0|max:1000000',
            'part' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle reservation allowance type logic
        $part = $request->input('part');
        $reservation_allowance_type = null;

        if (in_array('1', $part) && in_array('2', $part)) {
            $reservation_allowance_type = 3;  // Both '1' and '2' mean type 3 (both kinds of reservation)
        } elseif (in_array('1', $part)) {
            $reservation_allowance_type = 1;  // Only '1' (full reservation)
        } elseif (in_array('2', $part)) {
            $reservation_allowance_type = 2;  // Only '2' (partial reservation)
        }

        // Create the new sub-department
        $departement = new Departements();
        $departement->name = $request->name;
        $departement->manger = $request->manger;
        $departement->sector_id  = $request->sector;
        $departement->parent_id = $request->parent;  // Link this as a sub-department to its parent
        $departement->description = $request->description;
        $departement->reservation_allowance_amount = $request->budget;
        $departement->reservation_allowance_type = $reservation_allowance_type;
        $departement->created_by = Auth::user()->id;
        $departement->save();

        // Handle manager assignment (if a manager Civil Number is provided)
        // Handle manager assignment (if a manager Civil Number is provided)
        if ($request->manager) {
            $user = User::where('Civil_number', $request->manager)->first();

            if ($user) {
                // Check if the Civil Number belongs to any sector manager
                $isSectorManager = Sector::where('manager', $user->id)->exists();
                if ($isSectorManager) {
                    return redirect()->back()->withErrors('لا يمكن تعيين مدير قطاع كمدير أو موظف.')->withInput();
                }

                // Check if the manager is already in a department and confirm transfer if needed
                if ($user->department_id && $request->has('confirm_transfer')) {
                    // Transfer the manager to the new department
                    $user->sector = $request->sector;
                    $user->department_id = $departement->id;
                } elseif ($user->department_id) {
                    return redirect()->back()->withErrors('هذا المستخدم موجود بالفعل في قطاع آخر.')->withInput();
                }

                // Update the manager details
                if ($request->password) {
                    $user->flag = 'user';
                    $user->rule_id = $request->rule;
                    $user->password = Hash::make($request->password);
                }
                $user->save();

                // Send email notification to the manager
                Sendmail(
                    'مدير ادارة فرعية',
                    'تم أضافتك كمدير ادارة فرعية',
                    $user->Civil_number,
                    $request->password ? $request->password : null,
                    $user->email
                );
            } else {
                // Handle the case where no user is found with the provided Civil Number
                return redirect()->back()->with('error', 'هذا المستخدم غير موجود');
            }
        }

        $failed_civil_numbers = [];
        if ($request->has('Civil_number')) {
            $Civil_numbers = explode(',', str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number));
            foreach ($Civil_numbers as $Civil_number) {
                $employee = User::where('Civil_number', $Civil_number)->first();
                if ($employee) {
                    // Check if the employee is already assigned to a department and confirm transfer if needed
                    if ($employee->department_id && $request->has('confirm_transfer')) {
                        // Transfer the employee to the new department if confirmed
                        $employee->sector = $request->sector;
                        $employee->department_id = $departement->id;
                        $employee->save();
                    } elseif ($employee->department_id) {
                        // If not confirmed, add the Civil Number to failed list
                        $failed_civil_numbers[] = $Civil_number;
                    } else {
                        // If employee is not assigned to any department, assign them directly
                        $employee->sector = $request->sector;
                        $employee->department_id = $departement->id;
                        $employee->save();
                    }
                }
            }
        }

        // Prepare success message
        $message = 'تم أضافه ادارة فرعية جديدة';

        // Append failed Civil Numbers to the message, if any
        if (count($failed_civil_numbers) > 0) {
            $message .= ' لكن بعض الموظفين لم يتم إضافتهم بسبب عدم العثور على الأرقام المدنية أو عدم تأكيد النقل: ' . implode(', ', $failed_civil_numbers);
        }

        // Redirect with success message
        return redirect()->route('sub_departments.index', ['id' => $request->parent])
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $department = departements::with(['manager', 'managerAssistant', 'children', 'parent'])->findOrFail($id);
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(departements $department)
    {
        // dd($department);
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

        return view('sub_departments.edit', compact('department', 'managers', 'employees', 'sect'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, departements $department)
    {
        //dd($request);
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',

            'part.required' => 'نوع بدل الحجز مطلوب.',
            // 'part.numeric' => 'نوع بدل الحجز يجب أن يكون رقمًا.',
            // 'part.min' => 'نوع بدل الحجز يجب ألا يقل عن 0.01.',
            // 'part.max' => 'نوع بدل الحجز يجب ألا يزيد عن 1000000.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'required|numeric|min:0|max:1000000',
            'part' => 'required',

        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $part = $request->input('part');

        $reservation_allowance_type = null;

        if (in_array('1', $part) && in_array('2', $part)) {
            // If both '1' and '2' are present, save 3
            $reservation_allowance_type = 3;
        } elseif (in_array('1', $part)) {
            // If only '1' is present, save 1
            $reservation_allowance_type = 1;
        } elseif (in_array('2', $part)) {
            // If only '2' is present, save 2
            $reservation_allowance_type = 2;
        }
        $departements = Departements::findOrFail($department->id);
        $departements->name = $request->name;
        $departements->manger = $request->manger;
        $departements->sector_id  = $request->sector;
        $departements->description = $request->description;

        $departements->reservation_allowance_amount = $request->budget;
        $departements->reservation_allowance_type = $reservation_allowance_type;

        // $departements->parent_id = Auth::user()->department_id;
        $departements->created_by = Auth::user()->id;
        $departements->save();
        $oldManager = $department->manager;
        //  dd($request->all() );
        // Get all employees currently assigned to the department
        if ($oldManager->id != $request->manger) {
            // dd($request->manger ,$oldManager);

            // Update old manager's sector to null
            if ($oldManager) {
                $oldManagerUser = User::find($oldManager);
                if ($oldManagerUser) {
                    $oldManagerUser->sector = null;
                    $oldManagerUser->flag = 'employee';
                    $oldManagerUser->department_id = null;
                    $oldManagerUser->password = null;
                    $oldManagerUser->save();
                }
            }
            // Update new manager's sector
            if ($request->password) {
                $newManager = User::find($request->manger);
                if ($newManager) {
                    $newManager->sector = $request->sector;
                    $newManager->flag = 'user';
                    $newManager->department_id = $departements->id;
                    $newManager->rule_id = $request->rule;
                    $newManager->password = Hash::make($request->password);
                    $newManager->save();
                } else {
                    return redirect()->back()->with('خطأ', 'هذا المستخدم غير موجود');
                }
            } else {

                $user = User::find($request->manger);
                if ($user) {
                    $user->sector = $request->sector;
                    $user->department_id = $departements->id;
                    $user->save();

                    Sendmail(
                        'مدير ادارة',  // Email subject
                        'تم أضافتك كمدير ادارة',  // Email body
                        $user->Civil_number,
                        $request->password ? $request->password : null,
                        $user->email
                    );
                } else {
                    return redirect()->back()->with('خطأ', 'هذا المستخدم غير موجود');
                }
            }
        }

        // Handle employee Civil_number updates
        $currentEmployees = User::where('sector', $request->sector)
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
                $employee->sector = $request->sector;
                $employee->department_id = $departements->id;
                $employee->save();
            }
        }
        return redirect()->route('departments.index', ['id' => $request->sector])->with('success', 'Department updated successfully.');
        // return response()->json($department);
    }

    public function update_1(Request $request, departements $department)
    {
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',

            'part.required' => 'نوع بدل الحجز مطلوب.',
            // 'part.numeric' => 'نوع بدل الحجز يجب أن يكون رقمًا.',
            // 'part.min' => 'نوع بدل الحجز يجب ألا يقل عن 0.01.',
            // 'part.max' => 'نوع بدل الحجز يجب ألا يزيد عن 1000000.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'budget' => 'required|numeric|min:0|max:1000000',
            'part' => 'required',

        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $part = $request->input('part');

        $reservation_allowance_type = null;

        if (in_array('1', $part) && in_array('2', $part)) {
            // If both '1' and '2' are present, save 3
            $reservation_allowance_type = 3;
        } elseif (in_array('1', $part)) {
            // If only '1' is present, save 1
            $reservation_allowance_type = 1;
        } elseif (in_array('2', $part)) {
            // If only '2' is present, save 2
            $reservation_allowance_type = 2;
        }
        $departements = Departements::findOrFail($department->id);
        $departements->name = $request->name;
        $departements->manger = $request->manger;
        $departements->sector_id  = $request->sector_id;
        $departements->description = $request->description;
        $departements->parent_id = $department->parent_id;

        $departements->reservation_allowance_amount = $request->budget;
        $departements->reservation_allowance_type = $reservation_allowance_type;

        // $departements->parent_id = Auth::user()->department_id;
        $departements->created_by = Auth::user()->id;
        $departements->save();
        $allemployee = User::where('department_id', $request->parent)->whereNot('id', $department->manger)->pluck('id')->toArray();
        foreach ($allemployee as $item) {
            $use = User::find($item);

            if ($use) {
                $use->department_id = null;
                $use->save();
                // dd($user);
            }
        }

        $user = User::find($request->manger);
        if ($user) {
            $user->department_id = $departements->id;
            $user->save();

            Sendmail(
                'مدير ادارة فرعية',
                'تم أضافتك كمدير ادارة فرعية',
                $user->Civil_number,
                $request->password ? $request->password : null,
                $user->email
            );
        } else {
            return redirect()->back()->with('error', 'هذا المستخدم غير موجود');
        }

        if ($request->has('employess')) {
            foreach ($request->employess as $item) {
                // dd($item);
                $user = User::find($item);

                if ($user) {
                    $user->department_id = $departements->id;
                    $user->save();
                    // dd($user);
                }
            }
        }
        return redirect()->route('sub_departments.index', ['id' => $departements->parent])->with('success', 'Department updated successfully.');
        // return response()->json($department);
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
