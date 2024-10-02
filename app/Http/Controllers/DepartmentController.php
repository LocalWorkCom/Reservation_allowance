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
use App\Models\Sector;
use Carbon\Carbon;

class DepartmentController extends Controller
{
    public function index()
    {
        // if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {
        $users = User::where('flag', 'employee')->where('department_id', NULL)->get();

        $departments = departements::where('parent_id', Auth::user()->department_id)->first();
        return view('departments.index', compact('users', 'departments'));
        // }else{
        //     return redirect()->route('sub_departments.index',['id' => Auth::user()->department_id]);
        // }
    }
    public function getDepartment()
    {
        //department main all
        if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {
            $data = departements::where('parent_id', null)->withCount('iotelegrams')
                ->withCount('outgoings')
                ->withCount('children')
                ->with(['children'])
                ->orderBy('id', 'desc')->get();
        } else {
            $data = departements::where('id', auth()->user()->department_id)->withCount('iotelegrams')
                ->withCount('children')

                ->withCount('outgoings')->where(function ($query) {
                    $query->where('id', Auth::user()->department_id)
                        ->orWhere('parent_id', Auth::user()->department_id); // Include rows where 'rule_id' is null
                })
                ->with(['children'])
                ->orderBy('id', 'desc')->get();
        }


        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-primary btn-sm">Edit</button>';
            })
            // ->addColumn('iotelegrams_count', function ($row) {
            //     return $row->iotelegrams_count;  // Display the count of iotelegrams
            // })
            // ->addColumn('outgoings_count', function ($row) {
            //     return $row->outgoings_count;
            // })
            ->addColumn('reservation_allowance', function ($row) { // New column for departments count
                if ($row->reservation_allowance_type == 1) {
                    return 'حجز كلى';
                } elseif ($row->reservation_allowance_type == 2) {
                    return 'حجز جزئى';
                } else {
                    return 'حجز كلى و حجز جزئى';
                }
            }) ->addColumn('subDepartment', function ($row) { // New column for departments count
                $sub=departements::where('parent_id',$row->id)->count();
                return $sub;
            })
            ->addColumn('manager_name', function ($row) {
                return $row->manager ? $row->manager->name : 'لايوجد مدير للأداره'; // Display the manager's name
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function getManagerDetails($id)
    {
        // Fetch manager data from the database
        $manager = User::find($id);
        $joiningDate =$manager->joining_date ? Carbon::parse($manager->joining_date) : Carbon::parse($manager->created_at);
        // Get the current date
        $today = Carbon::now();

        // Calculate the difference in years
        $yearsOfService = $joiningDate->diffInYears($today);
        // Return the manager data in JSON format
        return response()->json([
            'rank' => $manager->grade_id ? $manager->grade->name : 'لا يوجد رتبه',
            'job_title' => $manager->job_title ?? 'لا يوجد مسمى وظيفى',
            'seniority' => $yearsOfService,
            'name' => $manager->name,
            'phone' => $manager->phone
        ]);
    }



    // public function index_1(subDepartmentsDataTable $dataTable)
    // {
    //     return $dataTable->render('sub_departments.index');
    //     // $departments = departements::with(['manager', 'managerAssistant'])->paginate(10);
    //     // return view('sub_departments.index', compact('departments'));
    //     // return response()->json($departments);
    // }

    public function index_1($id)
    {

        $users = User::where('flag', 'employee')->where('department_id', NULL)->get();
        $departments = departements::where('parent_id', $id)->get();
        $parentDepartment = departements::findOrFail($id);

        return view('sub_departments.index', compact('users', 'departments', 'parentDepartment'));
    }
    public function getSub_Department(Request $request, $id)
    {
        if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {
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
            ->addColumn('manager_name', function ($row) {
                return $row->manager ? $row->manager->name : 'لايوجد مدير للأداره';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //create Main Administration

        $sectors = Sector::all();
        $managers = User::where('flag', 'user')->where('department_id', 1)->whereNot('id', auth()->user()->id)->get();
        $employees = User::where('flag', 'employee')->where('department_id', null)->get();
        return view('departments.create', compact('sectors', 'managers', 'employees'));
    }


    public function create_1($id)
    {
        $department = departements::findOrFail($id);
        if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {
            $employees = User::where(function ($query) use ($id) {
                $query->where('department_id', $id)
                    ->orWhere('department_id', null);
            })
                ->where('flag', 'employee')
                ->whereNot('id', $department->manager)
                ->whereNot('id', auth()->user()->id)
                ->get();
            $managers = User::where('flag', 'user')->where('department_id', 1)->where('rule_id', 3)->get();
        } else {
            $employees = User::where(function ($query) use ($id) {
                $query->where('department_id', $id);
            })
                ->where('flag', 'employee')
                ->whereNot('id', $department->manager)
                ->whereNot('id', auth()->user()->id)
                ->get();
            $managers = User::where('flag', 'user')->where('department_id', $id)->whereNot('id', auth()->user()->id)->get();
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
            \Log::error('Error fetching employees: ' . $e->getMessage());
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
        //   dd($departements);
        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
        // return response()->json($department, 201);
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
        $sectors = Sector::all();
        $managers = User::where('flag', 'user')
            ->where('department_id', operator: 1)
            ->whereNot('id', auth()->user()->id)
            ->orWhere(function ($query) use ($department) {
                $query->where('id', $department->manger);
            })
            ->get();
        // dd(auth()->user()->id, $managers);
        $employees = User::where('flag', 'employee')
            ->where(function ($query) use ($department) {
                $query->where('department_id', null)
                    ->orWhere('department_id', $department->id);
            })
            ->whereNot('id', auth()->user()->id)
            ->whereNot('id', $department->manger)
            ->get();
        return view('departments.edit', compact('department', 'sectors', 'managers', 'employees'));
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
            $managers = User::where('flag', 'user')->where('department_id', 1)->orWhere('id', $department->manger)->where('rule_id', 3)->get();
        } else {
            $employees = User::where('department_id', $department->id)->whereNot('id', $department->manager)->get();
            $managers = User::where('flag', 'user')->where('department_id', 1)->get();
        }
        // dd(vars: $department);
        // $managers = User::where('flag', 'user')->where('department_id', 1)->orWhere('id', $department->manger)->get();
        // $employees = User::where('flag', 'employee')->where('department_id', null)->orWhere('department_id', $department->id)->whereNot('id', $department->manger)->get();

        return view('sub_departments.edit', compact('department', 'managers', 'employees','sect'));
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

        // Get all employees currently assigned to the department
        $currentEmployees = User::where('department_id', $departements->id)->where('flag', 'employee')->pluck('id')->toArray();
        $newEmployees = $request->has('employess') ? $request->employess : [];

        // Find employees that were removed
        $removedEmployees = array_diff($currentEmployees, $newEmployees);
        foreach ($removedEmployees as $item) {
            $user = User::find($item);
            if ($user) {
                $user->department_id = null; // Set department_id to null for removed employees
                $user->save();
            }
        }

        // Handle department manager change
        if ($request->manger != $department->manger) {
            // Reassign old manager's department_id to 1
            $oldManager = User::find($department->manger);
            if ($oldManager) {
                $oldManager->department_id = 1; // Reset old manager's department_id
                $oldManager->save();
            }

            // Update the new manager's department_id
            $newManager = User::find($request->manger);
            if ($newManager) {
                $newManager->department_id = $departements->id; // Set new manager's department_id
                $newManager->save();
            }
        }

        // Update department_id for new employees
        foreach ($newEmployees as $item) {
            $user = User::find($item);
            if ($user) {
                $user->department_id = $departements->id; // Set department_id for new employees
                $user->save();
            }
        }
        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
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
