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

use Illuminate\Support\Facades\Hash;

class DepartmentController extends Controller
{
    public function index()
    {
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
            // dd('d');
            $departments = departements::all();
        } elseif (Auth::user()->rule->id == 4) {
            $departments = departements::where('sector_id', auth()->user()->sector);
        } elseif (Auth::user()->rule->id == 3) {
            $departments = departements::where('id', auth()->user()->department_id);
        }
        return view("departments.index");
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
                    ->where('rule_id', 3)
                    ->count();
            })
            ->addColumn('num_subdepartment_managers', function ($row) {
                $subdepartment_ids = departements::where('parent_id', $row->id)->pluck('id');
                return User::whereIn('department_id', $subdepartment_ids)
                    ->where('rule_id', 3)
                    ->count();
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function getManagerDetails($id)
    {
        // Fetch manager data from the database
        $manager = User::where('Civil_number',$id)->first();

        if (!$manager) {
            return response()->json(['error' => 'عفوا هذا المستخدم غير موجود'], 404);
        }

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


    public function index_1($id)
    {

        $users = User::where('flag', 'employee')->where('department_id', NULL)->get();
        $departments = departements::where('parent_id', $id)->get();
        $parentDepartment = departements::findOrFail($id);

        return view('sub_departments.index', compact('users', 'departments', 'parentDepartment'));
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
                    ->where('rule_id', 3)
                    ->count();
            })

            ->addColumn('num_subdepartment_managers', function ($row) {
                $subdepartment_ids = departements::where('parent_id', $row->id)->pluck('id');
                return User::whereIn('department_id', $subdepartment_ids)
                    ->where('rule_id', 3)
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
            'mangered.required' => 'اسم المدير مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.01.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',

            'part.required' => 'نوع بدل الحجز مطلوب.',
            // 'part.numeric' => 'نوع بدل الحجز يجب أن يكون رقمًا.',
            // 'part.min' => 'نوع بدل الحجز يجب ألا يقل عن 0.01.',
            // 'part.max' => 'نوع بدل الحجز يجب ألا يزيد عن 1000000.',
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

        $user = User::find($request->mangered);
        if ($user) {
            $user->sector =  $request->sector;
            $user->department_id = $departements->id;
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
        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = explode(',,', $Civil_numbers);

        // Find employees based on Civil_number
        $employees = User::whereIn('Civil_number', $Civil_numbers)->pluck('id')->toArray();

        // Check if the selected manager is one of the employees
        if (in_array($request->mangered, $employees)) {
            return redirect()->back()->with('error', 'المدير لا يمكن أن يكون أحد الموظفين المدرجين.');
        }
        // Update employees in the sector
        foreach ($Civil_numbers as $Civil_number) {
            $employee = User::where('Civil_number', $Civil_number)->first();
            if ($employee && $employee->grade_id != null) {
                $employee->sector =  $request->sector;
                $employee->department_id = $departements->id;
                $employee->save();
            }
        }
        return redirect()->route('departments.index', ['id' => $request->sector])->with('success', 'Department created successfully.');
    }


    public function store_1(Request $request)
    {
        $messages = [
            'name.required' => 'اسم الحقل مطلوب.',
            'manger.required' => 'اسم المدير مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.01.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',

            'part.required' => 'نوع بدل الحجز مطلوب.',
            // 'part.numeric' => 'نوع بدل الحجز يجب أن يكون رقمًا.',
            // 'part.min' => 'نوع بدل الحجز يجب ألا يقل عن 0.01.',
            // 'part.max' => 'نوع بدل الحجز يجب ألا يزيد عن 1000000.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'manger' => 'required',
            'budget' => 'required|numeric|min:0.01|max:1000000',
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
        $departements = new Departements();
        $departements->name = $request->name;
        $departements->manger = $request->manger;
        $departements->sector_id  = $request->sector;
        $departements->description = $request->description;
        $departements->parent_id = $request->parent;

        $departements->reservation_allowance_amount = $request->budget;
        $departements->reservation_allowance_type = $reservation_allowance_type;

        // $departements->parent_id = Auth::user()->department_id;
        $departements->created_by = Auth::user()->id;
        $departements->save();

        $user = User::find($request->manger);
        $user->department_id = $departements->id;
        $user->save();

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
        return redirect()->route('sub_departments.index', ['id' => $request->parent])->with('success', 'Department created successfully.');
        // return response()->json($department, 201);
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
            'manger.required' => 'اسم المدير مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.01.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',

            'part.required' => 'نوع بدل الحجز مطلوب.',
            // 'part.numeric' => 'نوع بدل الحجز يجب أن يكون رقمًا.',
            // 'part.min' => 'نوع بدل الحجز يجب ألا يقل عن 0.01.',
            // 'part.max' => 'نوع بدل الحجز يجب ألا يزيد عن 1000000.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'manger' => 'required',
            'budget' => 'required|numeric|min:0.01|max:1000000',
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
            'manger.required' => 'اسم المدير مطلوب.',
            'budget.required' => 'مبلغ بدل الحجز مطلوب.',
            'budget.numeric' => 'مبلغ بدل الحجز يجب أن يكون رقمًا.',
            'budget.min' => 'مبلغ بدل الحجز يجب ألا يقل عن 0.01.',
            'budget.max' => 'مبلغ بدل الحجز يجب ألا يزيد عن 1000000.',

            'part.required' => 'نوع بدل الحجز مطلوب.',
            // 'part.numeric' => 'نوع بدل الحجز يجب أن يكون رقمًا.',
            // 'part.min' => 'نوع بدل الحجز يجب ألا يقل عن 0.01.',
            // 'part.max' => 'نوع بدل الحجز يجب ألا يزيد عن 1000000.',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'manger' => 'required',
            'budget' => 'required|numeric|min:0.01|max:1000000',
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
        $user->department_id = $departements->id;
        $user->save();

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
