<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $governmentIds = Government::pluck('id')->toArray(); // Get all government IDs
        $sectors = Sector::all(); // Retrieve all sectors

        // $sectorGovernmentIds = []; // Initialize an array to hold the sector government IDs

        // foreach ($sectors as $sector) {
        //     // Merge the current sector's government IDs into the $sectorGovernmentIds array
        //     $sectorGovernmentIds = array_merge($sectorGovernmentIds, $sector->governments_IDs);
        // }
        // // dd($governmentIds);
        // // Now $sectorGovernmentIds contains all the IDs from all sectors

        // // Check if all sector government IDs exist in the government IDs list
        // $allExist = !array_diff($governmentIds, $sectorGovernmentIds);

        //dd($allExist);
        // return view("sectors.index", compact('allExist'));
        return view("sectors.index");
    }

    public function getsectors()
    {
        $data = Sector::all();

        // foreach ($data as $sector) {
        //     $sector->government_names = Government::whereIn('id', $sector->governments_IDs)->pluck('name')->implode(', ');
        // }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                // $edit_permission = null;
                // $show_permission = null ;
                // if (Auth::user()->hasPermission('edit Sector')) {
                $edit_permission = '<a class="btn btn-sm" style="background-color: #F7AF15;"  href=' . route('sectors.edit', $row->id) . '><i class="fa fa-edit"></i> تعديل</a>';
                // }
                // if (Auth::user()->hasPermission('view Sector')) {
                $show_permission = '<a class="btn btn-sm" style="background-color: #274373;"  href=' . route('sectors.show', $row->id) . '> <i class="fa fa-eye"></i>عرض</a>';
                // }
                return $show_permission . ' ' . $edit_permission;
            })
            // ->addColumn('government_name', function ($row) {
            //     return $row->government_names;
            // })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function create()
    {
        $users = User::where('department_id', null)->where('sector',null)->get();
        $rules= Rule::whereNot('id',1)->get();
        return view('sectors.create', compact('users','rules'));
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

        $sector = new Sector();
        $sector->name = $request->name;
        $sector->reservation_allowance_type = $reservation_allowance_type;
        $sector->reservation_allowance_amount = $request->budget;
        $sector->manager  = $request->mangered;
        $sector->created_by = Auth::id();
        $sector->updated_by = Auth::id();
        $sector->save();

        if ($request->password) {
            // dd($request->all());
            $user = User::find($request->mangered);
            if ($user) {
                $user->sector = $sector->id;
                $user->flag = 'user';
                $user->department_id = null;
                $user->rule_id = $request->rule;
                $user->password = Hash::make($request->password);
                $user->save();
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
        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = explode(',,', $Civil_numbers);

        foreach ($Civil_numbers as $Civil_number) {
            $employee = User::where('Civil_number', $Civil_number)->first();

            if ($employee) {
                if ($employee->grade_id != null) {
                    $employee->sector = $sector->id;
                    $employee->department_id = null;
                    $employee->save();
                }
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
        $users = User::where('department_id', null)->orWhere( 'sector', $id)->get();

        return view('sectors.showdetails', compact('data','users'));
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
            'employees'=>$employees
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
        $currentEmployees = User::where('sector', $sector->id)->where('department_id',null)->pluck('Civil_number')->toArray();

        // Get the new employees from the request
        $Civil_numbers = str_replace(array("\r", "\r\n", "\n"), ',', $request->Civil_number);
        $Civil_numbers = array_filter(explode(',,', $Civil_numbers)); // Filter out any empty values

        // Employees to be removed (who are in the current sector but not in the new request)
        $employeesToRemove = array_diff($currentEmployees, $Civil_numbers);

        // Set the sector to null for removed employees
        if (!empty($employeesToRemove)) {
            User::whereIn('Civil_number', $employeesToRemove)->update(['sector' => null , 'department_id' => null]);
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
