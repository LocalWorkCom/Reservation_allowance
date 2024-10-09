<?php

namespace App\Http\Controllers;

use App\DataTables\gradeDataTable;
use App\DataTables\jobDataTable;
use App\DataTables\VacationDataTable;
use App\DataTables\vacationTypeDataTable;
use App\Http\Controllers\Controller;
use App\Models\Government;
use App\Models\grade;
use App\Models\job;
use Illuminate\Validation\Rule; // Ensure this is at the top
use App\Models\Setting;
use App\Models\VacationType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Country;


class settingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    //START JOB
    //show JOB
    public function indexjob()
    {
        return view("jobs.index");
    }
    //create JOB
    public function createjob()
    {
        return view("jobs.add");
    }

    //get data for JOB
    public function getAllJob()
    {
        $data = job::orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->get();

        return DataTables::of($data)->addColumn('action', function ($row) {
            $name = "'$row->name'";
            $edit_permission = null;
            $delete_permission = null;
            if (Auth::user()->hasPermission('edit job')) {
                $edit_permission = '<a class="btn btn-sm"  style="background-color: #F7AF15;"  onclick="openedit(' . $row->id . ',' . $name . ')">  <i class="fa fa-edit"></i> تعديل </a>';
            }
            if (Auth::user()->hasPermission('delete job')) {
                $delete_permission = ' <a class="btn  btn-sm" style="background-color: #C91D1D;"   onclick="opendelete(' . $row->id . ')"> <i class="fa-solid fa-trash"></i> حذف</a>';
            }
            $uploadButton = $edit_permission . $delete_permission;
            return $uploadButton;
        })
            ->rawColumns(['action'])
            ->make(true);
    }
    //add JOB
    public function addJob(Request $request)
    {
        $rules = [
            'nameadd' => 'required|string',
        ];

        $messages = [
            'nameadd.required' => 'يجب ادخال الوظيفه ',
        ];

        $validatedData = Validator::make($request->all(), $rules, $messages);
        if ($validatedData->fails()) {
            return response()->json(['success' => false, 'message' => $validatedData->errors()]);
        }
        $requestinput = $request->except('_token');
        $job = new job();
        $job->name = $request->nameadd;
        $job->save();
        $message = "تم اضافه الوظيفه";
        return redirect()->route('job.index', compact('message'));
        //return redirect()->back()->with(compact('activeTab','message'));
    }
    //show JOB
    public function showjob($id)
    {
        $data = job::findOrFail($id);
        return view("jobs.show", compact("data"));
    }
    //edit JOB
    public function editjob($id)
    {
        $data = job::findOrFail($id);
        return view("jobs.edit", compact("data"));
    }
    //update JOB
    public function updateJob(Request $request)
    {
        $job = job::find($request->id);

        if (!$job) {
            return response()->json(['error' => 'Grade not found'], 404);
        }
        $job->name = $request->name;
        $job->save();
        $message = '';
        return redirect()->route('job.index', compact('message'));
        // return redirect()->back()->with(compact('activeTab'));

    }

    //delete JOB
    public function deletejob(Request $request)
    {

        $isForeignKeyUsed = DB::table('users')->where('job_id', $request->id)->exists();
        //dd($isForeignKeyUsed);
        if ($isForeignKeyUsed) {
            return redirect()->route('job.index')->with(['message' => 'لا يمكن حذف هذه الوظيفه يوجد موظفين لها']);
        } else {
            $type = job::find($request->id);
            $type->delete();
            return redirect()->route('job.index')->with(['message' => 'تم حذف الوظيفه']);
        }
    }
    //END JOB
// start Nationality
public function indexbationality(Request $request)
{
    return view("nationality.index");
}
  //get data for GRAD
  public function getAllNationality(Request $request)
  {
      $data = Country::orderBy('country_name_en', 'ASC')->get();

      // Get the filtered data
    //   $data = $data->get();
    //dd($data[0]);
      return DataTables::of($data)
          ->addColumn('action', function ($row) {
              // Safely handle null values for JavaScript function parameters
              $name = $row->country_name_ar ? "'$row->country_name_en'" : "''";
              $order = $row->code ? "'$row->code'" : "''";


              $edit_permission = null;
              $delete_permission = null;

              if (Auth::user()->hasPermission('edit grade')) {
                  // Pass values safely to the JavaScript function
                  $edit_permission = '<a class="btn btn-sm" style="background-color: #F7AF15;" onclick="openedit(' . $row->id . ',' . $name . ',\'' . $row->code .'\')">  <i class="fa fa-edit"></i> تعديل </a>';
              }
              if (Auth::user()->hasPermission('delete grade')) {
                  $delete_permission = ' <a class="btn btn-sm" style="background-color: #C91D1D;" onclick="opendelete(' . $row->id . ')"> <i class="fa-solid fa-trash"></i> حذف</a>';
              }

              $uploadButton = $edit_permission . $delete_permission;
              return $uploadButton;
          })

          ->rawColumns(['action'])
          ->make(true);
  }

  public function createnationality()
  {
      return view("nationality.add");
  }
  //add nationality
  public function addNationality(Request $request)
  {
      $messages = [
          'nameadd.required' => 'الاسم مطلوب.',
          'nameadd.string' => 'الاسم يجب أن يكون نصًا.',

      ];

      // Create a validator instance
      $validator = Validator::make($request->all(), [
          'nameadd' => 'required|string',

      ], $messages);

      // Check if validation fails
      if ($validator->fails()) {
          // Set the session variable for the modal type
          session(['modal_type' => 'add']);

          // Redirect back with errors and input
          return redirect()->back()->withErrors($validator)->withInput();
      }
      session(['modal_type' => 'add']);
      $requestinput = $request->except('_token');
      $countries = new Country();
      $countries->country_name_ar = $request->nameadd;//
      $countries->country_name_en = $request->nameadd;
      $countries->country_name_fr = $request->nameadd;

      $countries->code = $request->codeAdd;

      $countries->save();
      $message = "تم اضافه الدولة";
      return redirect()->route('nationality.index', compact('message'));
  }
  //show nationality
  public function shownationality($id)
  {
      $data = grade::findOrFail($id);
      return view("grads.show", compact("data"));
  }
  //edit nationality
  public function editnationality($id)
  {
      $data = grade::findOrFail($id);
      return view("grads.edit", compact("data"));
  }
  //update nationality
  public function updatenationality(Request $request)
  {

      $messages = [
          'name.required' => 'الاسم مطلوب.',
          'name.string' => 'الاسم يجب أن يكون نصًا.',
      ];

      $countryId = $request->id; // Retrieve the ID from the route parameter
      // Create a validator instance
      $validator = Validator::make($request->all(), [
          'name' => 'required|string',

      ], $messages);

      if ($validator->fails()) {
          session(['modal_type' => 'edit']);

          session([
              'old_name' => $request->name,
              'old_codeedit' => $request->codeedit,

              'edit_id' => $request->id,
          ]);

          return redirect()->back()->withErrors($validator)->withInput();
      }

      $country = Country::find($request->id);

      if (!$country) {
          return response()->json(['error' => 'عفوا هذه الدولة غير موجوده'], 404);
      }

      $country->name = $request->name;
      $country->code = $request->codeedit;


      $country->save();
      $message = 'تم تعديل الدولة';
      return redirect()->route('nationality.index', compact('message'));
  }

  //delete nationality
  public function deletenationality(Request $request)
  {

      $isForeignKeyUsed = DB::table('users')->where('nationality_id', $request->id)->exists();
      //dd($isForeignKeyUsed);
      if ($isForeignKeyUsed) {
          return redirect()->route('nationality.index')->with(['message' => 'لا يمكن حذف هذه الجنسية يوجد موظفين لها']);
      } else {
          $type = Country::find($request->id);
          $type->delete();
          return redirect()->route('grads.index')->with(['message' => 'تم حذف الجنسية']);
      }
  }
  //END Nationality
    //START GRAD
    //show GRAD
    public function indexgrads()
    {
        $all = grade::count();

        $Officer = grade::where('type', 0)->count();
        $Officer2 = grade::where('type', 1)->count();
        $person = grade::where('type', 2)->count();
        return view("grads.index", compact('all', 'Officer', 'Officer2', 'person'));
    }
    //create GRAD


    //get data for GRAD
    public function getAllgrads(Request $request)
    {
        $data = grade::orderBy('order', 'ASC');
        $filter = $request->get('filter'); // Retrieve filter
        // dd($filter);
        // Apply the filter based on the type
        if ($filter == 'assigned') {
            $data->where('type', 1);
        } elseif ($filter == 'unassigned') {
            // Filter for type 1 and 2
            $data->whereIn('type', [2, 3]);
        }

        // Get the filtered data
        $data = $data->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                // Safely handle null values for JavaScript function parameters
                $name = $row->name ? "'$row->name'" : "''";
                $order = $row->order ? "'$row->order'" : "''";
                $value_all = $row->value_all ? "'$row->value_all'" : "''";
                $value_part = $row->value_part ? "'$row->value_part'" : "''";

                $edit_permission = null;
                $delete_permission = null;

                if (Auth::user()->hasPermission('edit grade')) {
                    // Pass values safely to the JavaScript function
                    $edit_permission = '<a class="btn btn-sm" style="background-color: #F7AF15;" onclick="openedit(' . $row->id . ',' . $name . ',' . $row->type . ',' . $value_all . ',' . $value_part . ',' . $order . ')">  <i class="fa fa-edit"></i> تعديل </a>';
                }
                if (Auth::user()->hasPermission('delete grade')) {
                    $delete_permission = ' <a class="btn btn-sm" style="background-color: #C91D1D;" onclick="opendelete(' . $row->id . ')"> <i class="fa-solid fa-trash"></i> حذف</a>';
                }

                $uploadButton = $edit_permission . $delete_permission;
                return $uploadButton;
            })
            ->addColumn('type', function ($row) {
                if ($row->type == 2) $mode = 'ظابط';
                elseif ($row->type == 1) $mode = ' فرد';
                else $mode = 'مهني';
                return $mode;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function creategrads()
    {
        return view("grads.add");
    }
    //add GRAD
    public function addgrads(Request $request)
    {
        $messages = [
            'nameadd.required' => 'الاسم مطلوب.',
            'nameadd.string' => 'الاسم يجب أن يكون نصًا.',
            'typeadd.required' => 'نوع الرتبة مطلوب.',
            'typeadd.string' => 'نوع الرتبة يجب أن يكون نصًا.',
            'value_all.required' => 'قيمه الدوام الكلى مطلوبة.',
            'value_all.numeric' => 'قيمه الدوام الكلى يجب أن تكون رقمًا.',
            'value_all.min' => 'قيمه الدوام الكلى يجب أن تكون أكبر من 0.01.',
            'value_all.max' => 'قيمه الدوام الكلى يجب ألا تتجاوز 1000000.',
            'value_part.required' => 'قيمه الدوام الجزئى مطلوبة.',
            'value_part.numeric' => 'قيمه الدوام الجزئى يجب أن تكون رقمًا.',
            'value_part.min' => 'قيمه الدوام الجزئى يجب أن تكون أكبر من 0.01.',
            'value_part.max' => 'قيمه الدوام الجزئى يجب ألا تتجاوز 1000000.',
            'order.required' => 'الترتيب حقل مطلوب.',
            'order.unique' => 'قيمة الترتيب مستخدمة بالفعل. الرجاء إدخال ترتيب مختلف.'
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'nameadd' => 'required|string',
            'typeadd' => 'required|string',
            'value_all' => 'required|numeric|min:0.01|max:1000000',
            'value_part' => 'required|numeric|min:0.01|max:1000000',
            'order' => 'required|unique:grades,order',

        ], $messages);

        // Check if validation fails
        if ($validator->fails()) {
            // Set the session variable for the modal type
            session(['modal_type' => 'add']);

            // Redirect back with errors and input
            return redirect()->back()->withErrors($validator)->withInput();
        }
        session(['modal_type' => 'add']);
        $requestinput = $request->except('_token');
        $grade = new grade();
        $grade->name = $request->nameadd;
        $grade->type = $request->typeadd;
        $grade->order = $request->order;

        $grade->value_all = $request->value_all;
        $grade->value_part = $request->value_part;
        $grade->save();
        $message = "تم اضافه الرتبه";
        return redirect()->route('grads.index', compact('message'));
        //return redirect()->back()->with(compact('activeTab','message'));
    }
    //show GRAD
    public function showgrads($id)
    {
        $data = grade::findOrFail($id);
        return view("grads.show", compact("data"));
    }
    //edit GRAD
    public function editgrads($id)
    {
        $data = grade::findOrFail($id);
        return view("grads.edit", compact("data"));
    }
    //update GRAD
    public function updategrads(Request $request)
    {

        $messages = [
            'name.required' => 'الاسم مطلوب.',
            'name.string' => 'الاسم يجب أن يكون نصًا.',
            'typeedit.required' => 'نوع الرتبة مطلوب.',
            'typeedit.string' => 'نوع الرتبة يجب أن يكون نصًا.',
            'value_alledit.required' => 'قيمه الدوام الكلى مطلوبة.',
            'value_alledit.numeric' => 'قيمه الدوام الكلى يجب أن تكون رقمًا.',
            'value_alledit.min' => 'قيمه الدوام الكلى يجب أن تكون أكبر من 0.01.',
            'value_alledit.max' => 'قيمه الدوام الكلى يجب ألا تتجاوز 1000000.',
            'value_partedit.required' => 'قيمه الدوام الجزئى مطلوبة.',
            'value_partedit.numeric' => 'قيمه الدوام الجزئى يجب أن تكون رقمًا.',
            'value_partedit.min' => 'قيمه الدوام الجزئى يجب أن تكون أكبر من 0.01.',
            'value_partedit.max' => 'قيمه الدوام الجزئى يجب ألا تتجاوز 1000000.',
            'orderedit.required' => 'الترتيب حقل مطلوب.',
            'orderedit.unique' => 'قيمة الترتيب مستخدمة بالفعل. الرجاء إدخال ترتيب مختلف.'
        ];

        $gradeId = $request->id; // Retrieve the ID from the route parameter
        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'typeedit' => 'required|string',
            'value_alledit' => 'required|numeric|min:0.01|max:1000000',
            'value_partedit' => 'required|numeric|min:0.01|max:1000000',
            'orderedit' => [
                'required',
                Rule::unique('grades', 'order')->ignore($gradeId)
            ],
        ], $messages);

        if ($validator->fails()) {
            session(['modal_type' => 'edit']);

            session([
                'old_name' => $request->name,
                'old_typeedit' => $request->typeedit,
                'old_value_alledit' => $request->value_alledit,
                'old_value_partedit' => $request->value_partedit,
                'old_orderedit' => $request->orderedit,
                'edit_id' => $request->id,
            ]);

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $grade = Grade::find($request->id);

        if (!$grade) {
            return response()->json(['error' => 'عفوا هذه الرتبه غير موجوده'], 404);
        }

        $grade->name = $request->name;
        $grade->type = $request->typeedit;
        $grade->value_all = $request->value_alledit;
        $grade->value_part = $request->value_partedit;
        $grade->order = $request->orderedit;

        $grade->save();
        $message = 'تم تعديل الرتبه';
        return redirect()->route('grads.index', compact('message'));
    }

    //delete GRAD
    public function deletegrads(Request $request)
    {

        $isForeignKeyUsed = DB::table('users')->where('grade_id', $request->id)->exists();
        //dd($isForeignKeyUsed);
        if ($isForeignKeyUsed) {
            return redirect()->route('grads.index')->with(['message' => 'لا يمكن حذف هذه الرتبه يوجد موظفين لها']);
        } else {
            $type = grade::find($request->id);
            $type->delete();
            return redirect()->route('grads.index')->with(['message' => 'تم حذف الرتبه']);
        }
    }
    //END GRAD

    //START VACATION TYPE
    //show JOB
    public function indexvacationType()
    {

        return view("vacationType.index");
    }
    //create JOB
    public function createvacationType()
    {
        return view("vacationType.add");
    }

    //get data for JOB
    public function getAllvacationType()
    {

        $data = VacationType::orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->get();


        return DataTables::of($data)->addColumn('action', function ($row) {
            $hiddenIds = [1, 2, 3, 4];
            $name = "'$row->name'";
            $edit_permission = null;
            $delete_permission = null;
            if (Auth::user()->hasPermission('edit VacationType')) {
                $edit_permission = '<a class="btn btn-sm"  style="background-color: #F7AF15;"  onclick="openedit(' . $row->id . ',' . $name . ')">  <i class="fa fa-edit"></i> تعديل </a>';
            }
            if (Auth::user()->hasPermission('delete VacationType')) {
                if (!in_array($row->id, $hiddenIds)) {
                    $delete_permission = ' <a class="btn  btn-sm" style="background-color: #C91D1D;"   onclick="opendelete(' . $row->id . ')"> <i class="fa-solid fa-trash"></i> حذف</a>';
                }
            }
            return $edit_permission . $delete_permission;
        })
            ->rawColumns(['action'])
            ->make(true);
    }
    //add JOB
    public function addvacationType(Request $request)
    {
        $rules = [
            'nameadd' => 'required|string',
        ];

        $messages = [
            'nameadd.required' => 'يجب ادخال نوع الأجازه ',
        ];

        $validatedData = Validator::make($request->all(), $rules, $messages);
        if ($validatedData->fails()) {
            return response()->json(['success' => false, 'message' => $validatedData->errors()]);
        }
        $requestinput = $request->except('_token');
        //dd($request->nameadd);
        $job = new VacationType();
        $job->name = $request->nameadd;
        $job->save();

        $message = "تم اضافة نوع الأجازه";
        return redirect()->route('vacationType.index', compact('message'));
        //return redirect()->back()->with(compact('activeTab','message'));
    }
    //show JOB
    public function showvacationType($id)
    {
        $data = VacationType::findOrFail($id);
        return view("vacationType.show", compact("data"));
    }
    //edit JOB
    public function editvacationType(Request $request)
    {
        $data = VacationType::findOrFail($request->id);
        return view("vacationType.edit", compact("data"));
    }
    //update JOB
    public function updatevacationType(Request $request)
    {
        $job = VacationType::find($request->id);

        if (!$job) {
            return response()->json(['error' => 'هذه الأجازه غير موجوده'], 404);
        }
        $job->name = $request->name;
        $job->save();
        $message = 'تم تعديل نوع الأجازه';
        return redirect()->route('vacationType.index', compact('message'));
        // return redirect()->back()->with(compact('activeTab'));

    }

    //delete JOB
    public function deletevacationType(Request $request)
    {

        $isForeignKeyUsed = DB::table('employee_vacations')->where('vacation_type_id', $request->id)->exists();
        //dd($isForeignKeyUsed);
        if ($isForeignKeyUsed) {
            return redirect()->route('vacationType.index')->with(['message' => 'لا يمكن حذف هذه نوع الاجازه يوجد موظفين لها']);
        } else {
            $type = VacationType::find($request->id);
            $type->delete();
            return redirect()->route('vacationType.index')->with(['message' => 'تم حذف نوع الاجازه']);
        }
    }
    //END VACATION TYPE



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $yesterday = '2024-08-13';
        $today = '2024-08-14';
        $allGovernments = Government::pluck('id')->toArray();
        foreach ($allGovernments as $government) {
            $allAvailablePoints = Grouppoint::where('government_id', $government)->pluck('id')->toArray();
            $allGroupsForGovernment = Groups::where('government_id', $government)->select('id', 'points_inspector')->get();

            foreach ($allGroupsForGovernment as $group) {

                $groupTeams = InspectorMission::where('group_id', $group->id)
                    ->select('group_team_id', 'ids_group_point')->whereDate('date', $yesterday)
                    ->distinct('group_team_id')
                    ->get();

                foreach ($groupTeams as $groupTeam) {
                    $pointPerTeam = $group->points_inspector;
                    if (!empty($allAvailablePoints)) {

                        // Get random keys from the available points
                        $randomKeys = array_rand($allAvailablePoints, $pointPerTeam);

                        if ($pointPerTeam == 1) {

                            $randomKeys = [$randomKeys];
                        } else {
                            $randomKeys = array_rand($allAvailablePoints, $pointPerTeam);
                        }

                        // Map the random keys to their corresponding values in the $allAvailablePoints array
                        $pointTeam = array_map(function ($key) use ($allAvailablePoints) {
                            return $allAvailablePoints[$key];
                        }, $randomKeys);

                        $allAvailableteam = array_diff($pointTeam, $groupTeam->ids_group_point);

                        //dd(implode(', ', $pointTeam) . '-old-' . implode(', ', $groupTeam->ids_group_point) . '-diferent-' . implode(', ', $allAvailablePoint));
                        if (count($allAvailableteam) == count($pointTeam)) {

                            //dd($group->id . "  /   ".$groupTeam->group_team_id);
                            $upatedMissions = InspectorMission::where('group_id', $group->id)->where('group_team_id', $groupTeam->group_team_id)->where('date', $today)->pluck('id')->toArray();
                            foreach ($upatedMissions as $upatedMission) {
                                $upated = InspectorMission::find($upatedMission);
                                if ($upated) {

                                    // Update the ids_group_point field
                                    $upated->ids_group_point = $pointTeam;

                                    // Save the updated record
                                    $upated->save();
                                }
                                $allAvailablePoints = array_diff($allAvailablePoints, $pointTeam);
                                // dd($allAvailablePoints);
                            }
                            // dd('upatedMissions');
                        } else {

                            // dd('7' . "  / " . implode(', ', $allAvailablePoints));
                            if (count($allAvailablePoints) <= 1) {

                                $pointTeam = [];
                                break;
                            } else {
                                // Get random keys from the available points
                                $randomKeys = array_rand($allAvailablePoints, $pointPerTeam);
                                //dd('7 ^' . "  / " . implode(', ', $randomKeys));
                                if ($pointPerTeam == 1) {
                                    // If only one key is selected, convert it to an array
                                    $randomKeys = [$randomKeys];
                                }

                                // Map the random keys to their corresponding values in the $allAvailablePoints array
                                $pointTeam = array_map(function ($key) use ($allAvailablePoints) {
                                    return $allAvailablePoints[$key];
                                }, $randomKeys);
                                $allAvailableteam = array_diff($pointTeam, $groupTeam->ids_group_point);
                            }
                        }
                    } else {
                        // dd('k');
                        $upatedMissions = InspectorMission::where('group_id', $group->id)->where('group_team_id', $groupTeam->group_team_id)->where('date', $today)->pluck('id')->toArray();
                        foreach ($upatedMissions as $upatedMission) {
                            $upated = InspectorMission::find($upatedMission);
                            if ($upated) {
                                // Update the ids_group_point field
                                $upated->ids_group_point = [];

                                // Save the updated record
                                $upated->save();
                            }
                        }
                    }
                }
            }
        }
    }
    public function allSettings()
    {
        return view('setting.index');
    }
    public function getSettings()
    {
        $data = Setting::all();

        return DataTables::of($data)
            ->rawColumns(['action'])
            ->make(true);
    }


    public function CreateSetting(Request $request)
    {
        $messages = [
            'key.required' => 'الاسم مطلوب ولا يمكن تركه فارغاً.',
            'value.required' => 'القيمة مطلوبة',
            'key.unique' => 'الاسم موجود بالفعل'

        ];

        $validatedData = Validator::make($request->all(), [
            'key' => 'required|unique:settings,key',
            'value' => 'required',

        ], $messages);

        // dd($validatedData);
        // Handle validation failure
        if ($validatedData->fails()) {
            // session()->flash('errors', $validatedData->errors());
            return redirect()->back()->withErrors($validatedData)->withInput()->with('showModal', true);
        }
        $Setting = new Setting();
        $Setting->key = $request->key;
        $Setting->value = $request->value;
        $Setting->save();
        session()->flash('success', 'تم اضافه اعداد بنجاح.');

        return redirect()->route('settings.index');
    }
    public function UpdateSetting(Request $request)
    {
        // dd($request);
        $messages = [
            'key.required' => 'الاسم مطلوب ولا يمكن تركه فارغاً.',
            'value.required' => 'القيمة مطلوبة',

        ];

        $validatedData = Validator::make($request->all(), [
            'key' => 'required',
            'value' => 'required',

        ], $messages);

        // dd($validatedData);
        // Handle validation failure
        if ($validatedData->fails()) {
            // session()->flash('errors', $validatedData->errors());
            return redirect()->back()->withErrors($validatedData)->withInput()->with('showModal', true);
        }
        $Setting  = Setting::find($request->id_edit);
        $Setting->key = $request->key;
        $Setting->value = $request->value;
        $Setting->save();

        session()->flash('success', 'تم تعديل اعداد بنجاح.');

        return redirect()->route('settings.index');
    }
    public function deleteSetting(Request $request)
    {

        $setting = Setting::find($request->id);
        $setting->delete();

        session()->flash('success', 'تم حذف الاعداد بنجاح.');

        return redirect()->route('settings.index');
    }
}
