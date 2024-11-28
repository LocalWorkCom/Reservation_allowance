<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\job;
use App\Models\Rule;
use App\Models\User;
use App\Models\grade;
// use Illuminate\Validation\Rule;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Country;
use App\Models\Government;
use Illuminate\Support\Str;
use App\Models\departements;
use Illuminate\Http\Request;
use App\Models\Qualification;
use App\Models\ViolationTypes;
use Yajra\DataTables\DataTables;
use App\Rules\UniqueNumberInUser;
use App\DataTables\UsersDataTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\Validator;
use App\helper; // Adjust this namespace as per your helper file location

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportUser;
use App\Exports\ExportUser;
use App\Exports\UsersExport;
use App\Exports\UsersImportTemplate;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;

/**
 * Send emails
 */

use TCPDF;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(UsersDataTable $dataTable)
    // {
    //     $data = User::all();
    //     return DataTables::of($data)->make(true);
    //     // return $dataTable->render('user.view');



    // }
    public function index($flag, $type = 0, $id = 0, $status = 0)
    {
        addUuidToTable('users');

        // $department_id = $request->get('id'); // Fetch department_id from the request
        $Officer = 0;
        $Officer2 = 0;
        $person = 0;
        $all = 0;
        // Fetch all users in the specified department
        // $users = User::where('department_id', $department_id)->get();

        // Fetch grade counts based on user associations
        // $gradeIds = $users->pluck('grade_id'); // Get all grade IDs from users in this department

        if ($type == "department") {
            $search_id = Departements::where('uuid', $id)->first()->id;
        } elseif ($type == 'sector') {
            $search_id = Sector::where('uuid', $id)->first()->id;
        } elseif ($type == 'parent') {
            $search_id = Departements::where('uuid', $id)->first()->id;
        }


        $gradeall = Grade::pluck('id')->toArray();
        if ($type == "department") {
            $all = User::where('department_id', $search_id)->where('flag', $flag)->whereIn('grade_id', $gradeall)->count();

            $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
            $person = User::where('department_id', $search_id)->where('flag', $flag)->whereIn('grade_id', $gradeperson)->count();

            $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
            $Officer = User::where('department_id', $search_id)->where('flag', $flag)->whereIn('grade_id', $gradeOfficer)->count();

            $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();
            $Officer2 = User::where('department_id', $search_id)->where('flag', $flag)->whereIn('grade_id', $graseOfficer2)->count();
        } elseif ($type == "sector") {
            if ($status == 'null') {
                $all = User::where('sector', $search_id)->whereNull('department_id')->where('flag', $flag)->whereIn('grade_id', $gradeall)->count();
                $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
                $person = User::where('sector', $search_id)->whereNull('department_id')->where('flag', $flag)->whereIn('grade_id', $gradeperson)->count();
                $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
                $Officer = User::where('sector', $search_id)->whereNull('department_id')->where('flag', $flag)->whereIn('grade_id', $gradeOfficer)->count();
                $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();
                $Officer2 = User::where('sector', $search_id)->whereNull('department_id')->where('flag', $flag)->whereIn('grade_id', $graseOfficer2)->count();
            } elseif ($status == 'notnull') {
                $all = User::where('sector', $search_id)->whereNotNull('department_id')->where('flag', $flag)->whereIn('grade_id', $gradeall)->count();
                $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
                $person = User::where('sector', $search_id)->whereNotNull('department_id')->where('flag', $flag)->whereIn('grade_id', $gradeperson)->count();
                $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
                $Officer = User::where('sector', $search_id)->whereNotNull('department_id')->where('flag', $flag)->whereIn('grade_id', $gradeOfficer)->count();
                $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();
                $Officer2 = User::where('sector', $search_id)->whereNotNull('department_id')->where('flag', $flag)->whereIn('grade_id', $graseOfficer2)->count();
            }

            // $all = User::where('sector', $search_id)->where($cond_dep)->where('flag', $flag)->whereIn('grade_id', $gradeall)->count();
            // $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
            // $person = User::where('sector', $search_id)->where($cond_dep)->where('flag', $flag)->whereIn('grade_id', $gradeperson)->count();
            // $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
            // $Officer = User::where('sector', $search_id)->where($cond_dep)->where('flag', $flag)->whereIn('grade_id', $gradeOfficer)->count();
            // $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();
            // $Officer2 = User::where('sector', $search_id)->where($cond_dep)->where('flag', $flag)->whereIn('grade_id', $graseOfficer2)->count();

        } elseif ($type == "parent") {
            $subdepartment_ids = Departements::where('parent_id', $search_id)->pluck('id');
            $all = User::whereIn('department_id', $subdepartment_ids)->where('flag', $flag)->whereIn('grade_id', $gradeall)->count();

            $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
            $person = User::whereIn('department_id', $subdepartment_ids)->where('flag', $flag)->whereIn('grade_id', $gradeperson)->count();


            $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
            $Officer = User::whereIn('department_id', $subdepartment_ids)->where('flag', $flag)->whereIn('grade_id', $gradeOfficer)->count();

            $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();
            $Officer2 = User::whereIn('department_id', $subdepartment_ids)->where('flag', $flag)->whereIn('grade_id', $graseOfficer2)->count();

            // $all = User::where('flag', $flag)->whereIn('grade_id', $gradeall)->count();

            // // dd($all);
            // $gradeperson = Grade::where('type', 3)->pluck('id')->toArray();
            // $person = User::where('flag', $flag)->whereIn('grade_id', $gradeperson)->count();

            // $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
            // $Officer = User::where('flag', $flag)->whereIn('grade_id', $gradeOfficer)->count();

            // $graseOfficer2 = Grade::where('type', 1)->pluck('id')->toArray();
            // $Officer2 = User::where('flag', $flag)->whereIn('grade_id', $graseOfficer2)->count();

        } else {
            if (Auth::user()->rule->id == 4) {
                $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
                $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
                $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();

                if (auth()->user()->sector && !auth()->user()->department_id) {

                    $all = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $gradeall)->count();
                    $person = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $gradeperson)->count();
                    $Officer = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $gradeOfficer)->count();
                    $Officer2 = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $graseOfficer2)->count();
                } else if (auth()->user()->sector && auth()->user()->department_id) {

                    $all = User::where('flag', $flag)->where('sector', auth()->user()->sector)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $gradeall)->count();
                    $person = User::where('flag', $flag)->where('sector', auth()->user()->sector)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $gradeperson)->count();
                    $Officer = User::where('flag', $flag)->where('sector', auth()->user()->sector)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $gradeOfficer)->count();
                    $Officer2 = User::where('flag', $flag)->where('sector', auth()->user()->sector)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $graseOfficer2)->count();
                }
            } elseif (Auth::user()->rule->id == 3) {
                $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
                $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();
                $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
                if (auth()->user()->sector && !auth()->user()->department_id) {
                    $all = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $gradeall)->count();
                    $person = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $gradeperson)->count();
                    $Officer2 = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $graseOfficer2)->count();
                    $Officer = User::where('flag', $flag)->where('sector', auth()->user()->sector)->whereIn('grade_id', $gradeOfficer)->count();
                } else if (auth()->user()->sector && auth()->user()->department_id) {

                    $all = User::where('flag', $flag)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $gradeall)->count();
                    $person = User::where('flag', $flag)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $gradeperson)->count();
                    $Officer2 = User::where('flag', $flag)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $graseOfficer2)->count();
                    $Officer = User::where('flag', $flag)->where('department_id', auth()->user()->department_id)->whereIn('grade_id', $gradeOfficer)->count();
                }
            } elseif (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) {
                $all = User::where('flag', $flag)->whereIn('grade_id', $gradeall)->count();
                $gradeperson = Grade::where('type', 1)->pluck('id')->toArray();
                $person = User::where('flag', $flag)->whereIn('grade_id', $gradeperson)->count();

                $gradeOfficer = Grade::where('type', 2)->pluck('id')->toArray();
                $Officer = User::where('flag', $flag)->whereIn('grade_id', $gradeOfficer)->count();

                $graseOfficer2 = Grade::where('type', 3)->pluck('id')->toArray();
                $Officer2 = User::where('flag', $flag)->whereIn('grade_id', $graseOfficer2)->count();
            }
        }

        // Fetch related departments and sectors
        $departments = departements::all();
        $sectors = Sector::all();



        return view('user.view', compact('departments', 'sectors', 'Officer', 'Officer2', 'person', 'all', 'flag', 'type', 'id', 'status'));
    }


    public function add_employees(Request $request)
    {
        // $department_id = $request->department_id;
        $uuid = $request->department_id;
        $Civil_number = $request->Civil_number;

        $department_details = departements::where('uuid', $uuid)->first();
        $department_id = $department_details->id;
        $sector_id = $department_details->sector_id;
        // Find the user by Civil_number
        $user = User::where('Civil_number', $Civil_number)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'لا يوجد موظف باهذا الرقم المدني.']);
        }
        $department = departements::where('manger', $user->id)->first();
        if ($department) {
            $department->manger = null;
            $department->save();
        }
        $sector = sector::where('manager', $user->id)->first();
        if ($sector) {
            return redirect()->back()->withErrors(['error' => 'لا يمكن نقل المدير الي الموظف']);
        }

        $user->sector = $sector_id;
        $user->flag = 'employee';
        $user->password = null;
        $user->rule_id = null;

        // Check if the user exists


        // If the user is found, assign the department_id and save
        $user->department_id = $department_id;
        $user->save();
        UpdateUserHistory($user->id);
        addUserHistory($user->id, $department_id, $sector_id);

        return redirect()->route('user.employees', ['id' => $uuid, 'flag' => 'employee', 'type' => 'department']);
    }


    public function getUsers(Request $request)
    {
        $flag = $request->flag;
        $parentDepartment = Departements::find(Auth()->user()->department_id);

        $status =  $request->get('status');
        $filter = $request->get('filter'); // Retrieve filter
        $parent_department_id = 0;
        $sector_id = 0;
        $department_id = 0;
        if (request()->has('type') && $request->get('type') == 'department') {
            $id_department = $request->get('id'); // Fetch department_id from the request
            $department_id = Departements::where('uuid', $id_department)->first()->id;
        }

        if (request()->has('type') && $request->get('type') == 'sector') {
            $id_sector = $request->get('id'); // Fetch department_id from the request
            $sector_id = Sector::where('uuid', $id_sector)->first()->id;
        }

        if (request()->has('type') && $request->get('type') == 'parent') {
            $id_parent_department = $request->get('id'); // Fetch department_id from the request
            $parent_department_id = Departements::where('uuid', $id_parent_department)->first()->id;
        }

        // Apply the filter based on the type


        if (Auth::user()->rule->name == "localworkadmin") {
            $data = User::where('flag', $flag); // Start as a query
        } elseif (Auth::user()->rule->name == "superadmin") {
            $data = User::where('flag', $flag); // Start as a query for superadmins too
        } else {
            // dd($parentDepartment);
            if (!$parentDepartment) {
                $sector = Auth::user()->sector;
                $data = User::where('flag', $flag)->where('sector', $sector);
            } else {

                // if (is_null($parentDepartment->parent_id)) {
                // $subdepart = Departements::where('parent_id', $parentDepartment->id)->pluck('id')->toArray();
                if (auth()->user()->sector && auth()->user()->department_id) {

                    $data = User::where('flag', $flag)
                        ->Where('department_id', Auth()->user()->department_id)->where('sector', auth()->user()->sector);
                } else if (auth()->user()->sector && !auth()->user()->department_id) {
                    $data = User::where('flag', $flag)
                        ->where('sector', auth()->user()->sector);
                }
                // } else {
                //     $data = User::where('flag', $flag)->where('department_id', $parentDepartment->id);
                // }
            }
        }
        // dd(Input::get('sector_id'));

        // Apply additional filters using `request()->get()`
        if ($department_id) {
            $data = $data->where('department_id', $department_id);
        } elseif ($parent_department_id) {
            $subdepartment_ids = Departements::where('parent_id', $parent_department_id)->pluck('id');
            $data = $data->whereIn('department_id', $subdepartment_ids);
        }
        //dd($request->amp;type);
        if ($sector_id) {
            if ($status == 'null') {
                $data = $data->where('sector', $sector_id)->whereNull('department_id');
            } else if ($status == 'notnull') {
                $data = $data->where('sector', $sector_id)->whereNotNull('department_id');
            }
        }

        if (request()->has('Civil_number')) {
            if (request()->has('amp;Civil_number') != 1)
                $data = $data->where('Civil_number', request()->get('Civil_number'));
        }


        // $gradeIds = $users->pluck('grade_id'); // Get all grade IDs from users in this department
        if ($filter == 'all') {

            $all = Grade::pluck('id')->toArray();
            $data->whereIn('grade_id', $all);
        } elseif ($filter == 'person') {
            $person = Grade::where('type', 1)->pluck('id')->toArray();
            $data->whereIn('grade_id', $person);
        } elseif ($filter == 'Officer') {
            $Officer = Grade::where('type', 2)->pluck('id')->toArray();
            $data->whereIn('grade_id', $Officer);
            // dd($data->get());
        } elseif ($filter == 'Officer2') {
            $Officer2 = Grade::where('type', 3)->pluck('id')->toArray();
            $data->whereIn('grade_id', $Officer2);
        }
        // Finally, fetch the results
        $data = $data->orderby('grade_id', 'asc')->get();

        return DataTables::of($data)->addColumn('action', function ($row) {
            return $row;
        })
            ->addColumn('department', function ($row) {
                return Departements::where('id', $row->department_id)->pluck('name')->first();
            })
            ->addColumn('grade', function ($row) {
                return grade::where('id', $row->grade_id)->pluck('name')->first();
            })
            ->addColumn('sector', function ($row) {
                return sector::where('id', $row->sector)->pluck('name')->first();
            })
            ->rawColumns(['action'])
            ->make(true);
    }




    // public function login(Request $request)
    // {
    //     $messages = [
    //         'military_number.required' => 'رقم العسكري مطلوب.',
    //         'password.required' => 'كلمة المرور مطلوبة.',
    //     ];

    //     $validatedData = Validator::make($request->all(), [
    //         'military_number' => 'required|string',
    //         'password' => 'required|string',
    //     ], $messages);

    //     if ($validatedData->fails()) {
    //         return back()->withErrors($validatedData)->withInput();
    //     }

    //     $military_number = $request->military_number;
    //     $password = $request->password;

    //     // Check if the user exists
    //     $user = User::where('military_number', $military_number)->first();

    //     if (!$user) {
    //         return back()->with('error', 'الرقم العسكري لا يتطابق مع سجلاتنا');
    //     }

    //     // Check if the user has the correct flag
    //     if ($user->flag !== 'user') {
    //         return back()->with('error', 'لا يسمح لك بدخول الهيئة');
    //     }

    //     $credentials = $request->only('military_number', 'password');

    //     // Check if the user has logged in within the last two hours
    //     $twoHoursAgo = now()->subHours(6);

    //     if (Auth::attempt($credentials)) {
    //         // If the user has logged in within the last two hours, do not set the code
    //         if ($user->updated_at >= $twoHoursAgo) {

    //             $firstlogin = 0;
    //             if ($user->token == null) {
    //                 $firstlogin = 1;
    //                             $set = '123456789';
    //         $code = substr(str_shuffle($set), 0, 4);

    //         $msg = "يرجى التحقق من حسابك\nتفعيل الكود\n" . $code;

    //         $response = send_sms_code($msg, $user->phone, $user->country_code);
    //         $result = json_decode($response, true);

    //         if (isset($result['sent']) && $result['sent'] === 'true') {
    //             return view('verfication_code', compact('code', 'military_number', 'password'));
    //         } else {
    //             return back()->with('error', 'سجل الدخول مرة أخرى');
    //         }
    //                // return view('resetpassword', compact('military_number', 'firstlogin'));
    //             }

    //             Auth::login($user); // Log the user in
    //             return redirect()->route('home');
    //         }

    //         $set = '123456789';
    //         $code = substr(str_shuffle($set), 0, 4);

    //         $msg = "يرجى التحقق من حسابك\nتفعيل الكود\n" . $code;

    //         $response = send_sms_code($msg, $user->phone, $user->country_code);
    //         $result = json_decode($response, true);

    //         if (isset($result['sent']) && $result['sent'] === 'true') {
    //             return view('verfication_code', compact('code', 'military_number', 'password'));
    //         } else {
    //             return back()->with('error', 'سجل الدخول مرة أخرى');
    //         }
    //     }

    //     return back()->with('error', 'كلمة المرور لا تتطابق مع سجلاتنا');
    // }
    public function login(Request $request)
    {
        $messages = [
            'number.required' => 'رقم العسكري مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ];

        $validatedData = $request->validate([
            'number' => 'required|string',
            'password' => 'required|string',
        ], $messages);

        $number = $request->number;
        $password = $request->password;


        // Check if the user exists
        $user = User::where('military_number', $number)->orwhere('Civil_number', $number)->orwhere('file_number', $number)->first();

        if (!$user) {
            return back()->with('error', 'الرقم العسكري / الرقم المدنى لا يتطابق مع سجلاتنا')->withInput();
        }
        if ($user && $user->last_login == null && $user->password == null) {
            return redirect()->route('passwordManager',['number'=>$number])->with('error', 'يرجى أنشأ كلمه السر الخاصه بك')->withInput();
        }else{
            return redirect()->route('enterpassword',['number'=>$number]);

        }
        // Check if the user has the correct flag
        if ($user->flag !== 'user') {
            return back()->with('error', 'لا يسمح لك بدخول الهيئة')->withInput();
        }
        // $credentials = $request->only('number', 'password');
        $credentials = [
            'password' => $password
        ];
        // Use a custom login function
        if ($user->military_number === $number) {
            $credentials['military_number'] = $number;
        } elseif ($user->file_number === $number) {
            $credentials['file_number'] = $number;
        } else {
            $credentials['civil_number'] = $number;
        }
        $twoHoursAgo = now()->subHours(6);

        if (Auth::attempt($credentials)) {
            // to not send code
            if ($user->token == 'logined') {
                Auth::login($user); // Log the user in
                $update = User::find($user->id);
                $update->last_login = now();
                $update->save();
                //
                return redirect()->route('home');
            }
            //end code
            // if ($user->updated_at >= $twoHoursAgo) {
            //     if ($user->token == null) {
            //         $firstlogin = 1;

            //         $set = '123456789';
            //         $code = substr(str_shuffle($set), 0, 4);

            //         $msg = "يرجى التحقق من حسابك\nتفعيل الكود\n" . $code;
            //         $response = send_sms_code($msg, $user->phone, $user->country_code);
            //         $result = json_decode($response, true);

            //         // if (isset($result['sent']) && $result['sent'] === 'true') {
            //         //     return view('verfication_code', compact('code', 'military_number', 'password'));
            //         // } else {
            //         //     return back()->with('error', 'سجل الدخول مرة أخرى')->withInput();
            //         // }
            //     }
            // }

            Auth::login($user); // Log the user in
            $update = User::find($user->id);
            $update->last_login = now();
            $update->save();
            return redirect()->route('home');
        }

        return back()->with('error', 'كلمة المرور لا تتطابق مع سجلاتنا')->withInput();
    }

    public function setpasswordManager(Request $request){
        return view('setPasswordManager');

    }
    public function resend_code(Request $request)
    {
        $user = User::where('email', $request->number)->first();
        // Generate a verification code
        $set = '123456789';
        $code = substr(str_shuffle($set), 0, 4);
        $msg  = "يرجى التحقق من حسابك\nتفعيل الكود\n" . $code;
        $password = 123456;
        // Retrieve the user's email (assuming it's stored in the 'email' column)
        $number = $user->email;
        $user->code = $code;
        $user->save();
        $details = [
            'title' => 'كود التفعيل',
            'body' => "هذا هو كود التفعيل ",
            'username' => $user->file_number,
            'password' => null, // Pass the password if needed
            'code' => $code, // Pass code
        ];

        Mail::to($number)->send(new SendEmail($details));
        return  view('verfication_code', compact('code', 'number', 'password'));
    }

    public function verfication_code(Request $request)
    {
        // Validate incoming request data
        $validatedData = Validator::make($request->all(), [
            'verfication_code' => 'required', // Ensure verfication_code field is required
        ], [
            'verfication_code.required' => 'كود التفعيل مطلوب.',
        ]);

        // Check if validation fails
        if ($validatedData->fails()) {
            return view('verfication_code')->withErrors($validatedData)
                ->with('code', $request->code)
                ->with('number', $request->number)
                ->with('password', $request->password);
        }

        $code = $request->code;
        $number = $request->number;
        $password = $request->password;
        // dd($request->verfication_code);
        // dd($request->code, $request->verfication_code);
        // Check if the provided verification code matches the expected code
        if ($request->code === $request->verfication_code) {
            // Find the user by military number
            $user = User::where('email', $number)->first();

            // Save the activation code and password
            $user->code = $request->code;
            $user->save();


            // dd($user);
            // $firstlogin = 0;

            // Coming from forget_password2
            // if ($user->token == null) {
            //     $firstlogin = 1;
            //     return view('resetpassword', compact('number', 'firstlogin'));
            //     // }

            // } else {
            if (url()->previous() == route('forget_password2') || url()->previous() == route('resend_code') || url()->previous() == route('verfication_code')) {
                return view('resetpassword', compact('number'));
            } else {
                return redirect()->route('home');
            }
            // }
        } else {
            // If verification code does not match, return back with error message and input values
            return view('verfication_code')->withErrors('الكود خاطئ.')
                ->with('code', $code)
                ->with('number', $number)
                ->with('password', $password);
        }
    }



    public function forget_password2(Request $request)
    {
        $messages = [
            'number.required' => 'رقم الملف مطلوب.',
        ];

        $validatedData = Validator::make($request->all(), [
            'number' => 'required|string',
        ], $messages);

        if ($validatedData->fails()) {
            return back()->withErrors($validatedData)->withInput();
        }

        $user = User::where('file_number', $request->number)->first();

        if (!$user) {
            return back()->with('error', 'الرقم الملف لا يتطابق مع سجلاتنا');
        } elseif ($user->flag !== 'user') {
            return back()->with('error', 'لا يسمح لك بدخول الهيئة');
        } else {
            // Generate a verification code
            $set = '123456789';
            $code = substr(str_shuffle($set), 0, 4);
            $msg  = "يرجى التحقق من حسابك\nتفعيل الكود\n" . $code;
            $password = 123456;
            // Retrieve the user's email (assuming it's stored in the 'email' column)
            $number = $user->email;
            $user->code = $code;
            $user->save();
            $details = [
                'title' => 'كود التفعيل',
                'body' => "هذا هو كود التفعيل ",
                'username' => $user->file_number,
                'password' => null, // Pass the password if needed
                'code' => $code, // Pass code
            ];

            Mail::to($number)->send(new SendEmail($details));

            // Sendmail('تحديث الباسورد', ' تم تجديد الباسورد',  $request->number,  $code, $number);


            // After sending the email, redirect to the verification page with the code and other data
            return  view('verfication_code', compact('code', 'number', 'password'));
        }
    }
    public function reset_password(Request $request)
    {
        $messages = [
            'number.required' => 'رقم العسكري مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password_confirm.same' => 'تأكيد كلمة المرور يجب أن يتطابق مع كلمة المرور.',
        ];

        $validatedData = Validator::make($request->all(), [
            'number' => 'required|string',
            'password' => 'required|string',
            'password_confirm' => 'same:password',
        ], $messages);

        if ($validatedData->fails()) {
            return view('resetpassword')
                ->withErrors($validatedData)
                ->with('number', $request->number);
        }

        $user = User::where('email', $request->number)->orwhere('file_number', $request->number)->first();

        if (!$user) {
            return back()->with('error', 'الرقم العسكري المقدم لا يتطابق مع سجلاتنا');
        }
        if (Hash::check($request->password, $user->password) == true) {
            return view('resetpassword')
                ->withErrors('لا يمكن أن تكون كلمة المرور الجديدة هي نفس كلمة المرور الحالية')
                ->with('number', $request->number); // Define $firstlogin here if needed
        }

        $user->password = Hash::make($request->password);
        $user->save();
        Auth::login($user); // Log the user in

        return redirect()->route('home')->with('success', 'تم إعادة تعيين كلمة المرور بنجاح');
        // return redirect()->route('home')->with('user', auth()->user());

    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
        // return view('welcome');
    }

    /**
     * Show the form for creating a new resource.
     */
    // In your controller

    public function getGradesByViolationType(Request $request)
    {
        // Validate the request
        $request->validate([
            'violation_type' => 'required|string',
        ]);

        // Get the selected violation type from the request
        $violationTypeName = $request->input('violation_type');

        // Fetch grades based on the selected violation type
        if ($violationTypeName == 2) {

            $grades = Grade::where('type', 2)->get();
        } else {
            $grades = Grade::where('type', '<>', 2)->get();
        }

        // Return the grades as a JSON response
        return response()->json($grades);
    }

    public function create()
    {
        //
        $user = User::find(Auth::user()->id);
        $rule = Rule::where('hidden', '!=', "1")->get();
        $grades = grade::all();
        $job = job::all();
        $govermnent = Government::all();
        $countries = Country::all();

        $area = Region::all();
        $qualifications = Qualification::all();
        $violationTypeName = ViolationTypes::whereJsonContains('type_id', 0)->get();

        //         // Get the selected violation type from old input or set a default value
        //         $selectedViolationType = old('type_military', 'police'); // Default to 'police'

        //         // Fetch grades based on the selected violation type
        //         $grades = Grade::where('type', $selectedViolationType)->get();
        // dd( $violationTypeName  , $selectedViolationType , $grades);
        if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {
            $alluser = User::where('flag', 'employee')->get();
            $sectors = Sector::all();
        } else {
            $alluser = User::where('flag', 'employee')
                ->leftJoin('departements', 'departements.id', '=', 'users.department_id') // Use leftJoin to handle `department_id = null`
                ->where(function ($query) {
                    $query->where('users.department_id', Auth::user()->department_id) // Match user’s department
                        ->orWhere('departements.parent_id', Auth::user()->department_id) // Match department’s parent ID
                        ->orWhereNull('users.department_id'); // Include users without a department
                })
                ->select('users.*') // Ensure only `users` columns are selected
                ->get();
            $sectors = Sector::where('id',  Auth::user()->sector)->get();
        }

        if ($user->department_id == "NULL") {
            $department = departements::all();
        } else {
            if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {
                $alldepartment = departements::all();
            } else {
                $alldepartment = departements::where('id', $user->department_id)->orwhere('parent_id', $user->department_id)->get();
            }
        }
        // $alluser = User::where('department_id',$user->department_id)->where('flag','employee')->get();

        // $speificUsers = User::where('department_id',$user->department_id)->where('flag','employee')->get();
        // $permission_ids = explode(',', $rule_permisssion->permission_ids);
        // $allPermission = Permission::whereIn('id', $permission_ids)->get();
        // dd($allPermission);
        // $alldepartment = $user->createdDepartments;
        // return view('role.create',compact('allPermission','alldepartment'));
        return view('user.create', compact('alldepartment', 'rule', 'grades', 'job', 'alluser', 'govermnent', 'area', 'sectors', 'qualifications', 'countries', 'violationTypeName'));
    }

    public function GetDepartmentsBySector()
    {
        $id = $_GET['sector'];
        $data = departements::where('sector_id', $id);
        if (Auth::user()->rule->id == 3) {
            $data = $data->where('id', Auth::user()->department_id);
        }

        $data = $data->orderBy('id', 'desc')
            ->get();

        return $data;
    }
    public function unsigned(Request $request)
    {
        //
        $user = User::where('uuid', $request->id_employee)->first();
        UpdateUserHistory($user->id);
        $user = User::where('uuid', $request->id_employee)->first();
        $user->department_id  = Null;
        $user->sector  = Null;
        $user->flag  = 'employee';
        $user->save();
        // $id = 1;
        $department = departements::where('manger', $request->id_employee)->first();
        if ($department) {
            $department->manger = null;
            $department->save();
        }
        $sector = sector::where('manager', $request->id_employee)->first();
        if ($sector) {
            $sector->manager = null;
            $sector->save();
        }
        addUserHistory($user->id, null, null);


        return redirect()->back()->with('success', 'تم الغاء التعيين بنجاح');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'الاسم  مطلوب ولا يمكن تركه فارغاً.',
            'name.string' => 'الاسم  يجب أن يكون نصاً.',
            'Civil_number.unique' => 'رقم المدنى الذي أدخلته موجود بالفعل.',
            'file_number.unique' => 'رقم الملف الذي أدخلته موجود بالفعل.',
            'phone.required' => 'رقم الهاتف مطلوب ولا يمكن تركه فارغاً.',
            'phone.unique' => 'رقم الهاتف الذي أدخلته موجود بالفعل.',
            'phone.regex' => 'رقم الهاتف يجب ان يكون مكون من 8 اراقام',
            'flag.required' => 'يجب عليك اختيار نوع المستخدم',
            'grade_id.required' => 'يجب اختيار رتبه',
            'file_number.required' => 'رقم الملف مطلوب ولا يمكن تركه فارغاً.',
            'Civil_number.required' => 'رقم المدنى مطلوب ولا يمكن تركه فارغاً   .',
            'email.unique' => 'الايميل الذي أدخلته موجود بالفعل.',

        ];

        $rules = [
            'phone' => [
                'required',
                'regex:/^[0-9]{8}$/',  // Ensures exactly 8 numeric digits
                ValidationRule::unique('users', 'phone'),
            ],
            'flag' => 'required',
            'name' => 'required|string',
            // 'department_id' => 'required',
            'Civil_number' => [
                'max:12',
                'required',
                ValidationRule::unique('users', 'Civil_number'),
            ],
            ' email' => [
                'nullable',
                ValidationRule::unique('users', 'email'),
            ],
            'file_number' => [
                'required',
                ValidationRule::unique('users', 'file_number'),
            ],
            'grade_id' => [
                'required',
            ],
            /*   'military_number' => [
                   'required_if:type_military,police',
                ], */
        ];
        if ($request->hasFile('image')) {
            // Save the new image and get its path
            $imagePath = $request->file('image')->store('images', 'public');
        } else {
            // If no new image is uploaded, keep the old image path
            $imagePath = $request->old('image', 'default_image.jpg'); // Adjust as necessary
        }
        if ($request->filled('email')) {
            $rules['email'] = [ValidationRule::unique('users', 'email'), 'email'];
            $messages['email.email'] = 'البريد الالكتروني يجب ان يكون يحتوي علي @ .com';
            $messages['email.unique'] = 'البريد الالكتروني الذي أدخلته موجود بالفعل.';
        }


        $validatedData = Validator::make($request->all(), $rules, $messages);



        // Handle validation failure
        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }

        $newUser = new User();
        $newUser->name = $request->name;
        if ($request->flag == 'user') {
            if (!$request->email) {
                return redirect()->back()->withErrors(['email' => 'البريد الالكتروني  مطلوب ولا يمكن تركه فارغاً.'])->withInput();
            }
            if (!$request->rule_id) {
                return redirect()->back()->withErrors(['rule_id' => 'المهام مطلوبة ولا يمكن تركها فارغة'])->withInput();
            }
            if (!$request->password) {
                return redirect()->back()->withErrors(['password' => 'كلمة المرور ولا يمكن تركها فارغة'])->withInput();
            }
            if (!$request->sector && !$request->department_id) {
                return redirect()->back()->withErrors(['department_id' => 'يجب عليك اختيار قطاع او ادارة علي الاقل'])->withInput();
            }
        }
        if ($request->filled('department_id')) {
            $id_department = Departements::where('uuid', $request->department_id)->value('id');
        } else {
            $id_department = null;
        }
        $newUser->email = $request->email;
        $newUser->type = $request->gender;
        $newUser->address1 = $request->address_1;
        $newUser->address2 = $request->address_2;
        $newUser->Provinces = $request->Provinces;
        $newUser->sector = $request->sector;
        $newUser->region = $request->region;
        $newUser->military_number = $request->military_number;
        $newUser->phone = $request->phone;
        $newUser->job_title = $request->job_title;
        $newUser->nationality = $request->nationality;
        $newUser->Civil_number = $request->Civil_number;
        $newUser->seniority = $request->seniority;
        $newUser->department_id = $id_department;
        $newUser->public_administration = $request->public_administration;
        $newUser->work_location = $request->work_location;
        $newUser->qualification = $request->qualification;
        $newUser->date_of_birth = $request->date_of_birth;
        $newUser->joining_date = $request->joining_date;
        $newUser->length_of_service = $request->end_of_service;
        $newUser->description = $request->description;
        $newUser->file_number = $request->file_number;
        if ($request->flag == 'user') {
            $newUser->rule_id = $request->rule_id;
            $newUser->password = Hash::make($request->password);
        }

        $newUser->flag = $request->flag;
        $newUser->grade_id = $request->grade_id;
        if ($request->has('job')) {
            $newUser->job_id = $request->job;
        }

        $newUser->save();

        if ($request->rule_id == 3) {
            $department = Departements::where('id', $id_department)->first();
            $department->manger = $newUser->id;
            $department->save();
            if ($newUser->email && isValidEmail($newUser->email)) {
                Sendmail('مدير أداره', ' تم أضافتك كمدير أداره' . $request->name, $newUser->Civil_number, $request->password, $newUser->email);
            }
        } elseif ($request->rule_id == 4) {
            $sector = Sector::where('id', $request->sector)->first();
            $sector->manager = $newUser->id;
            $sector->save();
            if ($newUser->email && isValidEmail($newUser->email)) {
                Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $newUser->Civil_number, $request->password, $newUser->email);
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->image;
            $path = 'users/user_profile';

            UploadFilesWithoutReal($path, 'image', $newUser, $file);
        }

        session()->flash('success', 'تم الحفظ بنجاح.');
        if ($request->email) {

            Sendmail('بيانات دخولك على نظام القوة المطور', 'هذه بيانات دخولك على نظام القوة المطور',  $request->Civil_number, $request->password, $request->email);
        }
        UpdateUserHistory($newUser->id);
        addUserHistory($newUser->id,  $id_department,  $request->sector);
        UpdateUserGradeHistory($newUser->id);
        addUserGradeHistory($newUser->id, $newUser->grade_id);
        // $id = $request->type;
        return redirect()->route('user.employees', $request->flag);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
        //$user = User::find($id);
        $rule = Rule::where('hidden', '!=', "1")->get();
        $grade = grade::all();
        $joining_date = Carbon::parse($user->joining_date);
        $end_of_serviceUnit = $joining_date->addYears($user->length_of_service);
        $end_of_service = $end_of_serviceUnit->format('Y-m-d');
        $job = job::all();
        $govermnent = Government::all();
        $area = Region::all();
        $sector = Sector::all();
        // $countries = Country::all();
        $countries = Country::all();
        $qualifications = Qualification::all();
        // dd($user);
        // if ($user->flag == "user") {
        //     $department = departements::where('id', $user->department_id)->get();
        // } else {
        //     $department = departements::where('id', $user->public_administration)->orwhere('parent_id', $user->public_administration)->get();
        // }
        // $department = departements::all();
        $department = departements::where('id', $user->department_id)->first();
        $hisdepartment = $user->createdDepartments;
        return view('user.show', compact('user', 'rule', 'grade', 'department', 'hisdepartment', 'end_of_service', 'job', 'sector', 'area', 'govermnent', 'qualifications', 'countries'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
        //$user = User::find($id);
        $rule = Rule::where('hidden', '!=', "1")->get();
        $grade = grade::all();
        $joining_date = Carbon::parse($user->joining_date);
        $end_of_serviceUnit = $joining_date->addYears($user->length_of_service);
        $end_of_service = $end_of_serviceUnit->format('Y-m-d');
        $job = job::all();
        $govermnent = Government::all();
        $area = Region::all();
        $sectors = Sector::all();
        $countries = Country::all();
        $qualifications = Qualification::all();

        // Fetch all violation types regardless of the user's grade
        $violationTypeName = ViolationTypes::whereJsonContains('type_id', 0)->get();

        // Get the selected violation type from the user (if it exists)
        // $selectedViolationType = old('type_military', $user->type_military); // Default to old input or user's current value

        // Fetch grades based on the selected violation type
        if ($user->grade) {

            $grades = Grade::where('type', $user->grade->type)->get();
        } else {
            $grades = [];
        }
        if ($user->department_id == "NULL") {
            $department = departements::all();
        } else {
            if (Auth::user()->rule->name == "localworkadmin" || Auth::user()->rule->name == "superadmin") {
                $department = departements::where('sector_id', $user->sector)->get();
            } else {
                $department = departements::where('id', $user->department_id)->orwhere('parent_id', $user->department_id)->get();
            }
        }
        // $department = departements::all();
        $hisdepartment = $user->createdDepartments;
        return view('user.edit', compact('user', 'rule', 'grade', 'grades', 'department', 'hisdepartment', 'violationTypeName', 'end_of_service', 'job', 'sectors', 'area', 'govermnent', 'qualifications', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, User $user)
    {
        //$user = User::findOrFail($id);  // Ensure user is found or throw error
        $military_number = $request->military_number;

        // Define validation messages
        $messages = [
            'name.required' => 'الاسم مطلوب ولا يمكن تركه فارغاً.',
            'name.string' => 'الاسم يجب أن يكون نصاً.',
            'Civil_number.unique' => 'رقم المدني الذي أدخلته موجود بالفعل.',
            'file_number.unique' => 'رقم الملف الذي أدخلته موجود بالفعل.',
            'phone.required' => 'رقم الهاتف مطلوب ولا يمكن تركه فارغاً.',
            'phone.unique' => 'رقم الهاتف الذي أدخلته موجود بالفعل.',
            'phone.regex' => 'رقم الهاتف يجب أن يتكون من 8 أرقام.',
            'flag.required' => 'يجب عليك اختيار نوع المستخدم.',
            'grade_id.required' => 'يجب اختيار رتبة.',
            'file_number.required' => 'رقم الملف مطلوب ولا يمكن تركه فارغاً.',
            'Civil_number.required' => 'رقم المدني مطلوب ولا يمكن تركه فارغاً.',
            'Civil_number.max' => 'رقم المدني يجب الا يتخطي ال 12 رقم',
            'email.email' => 'البريد الإلكتروني يجب أن يحتوي على @ و .com.',
            'email.unique' => 'البريد الإلكتروني الذي أدخلته موجود بالفعل.',
        ];

        // Define validation rules
        $rules = [
            'name' => 'required|string',
            'phone' => [
                'required',
                'regex:/^[0-9]{8}$/',  // Exactly 8 digits
                ValidationRule::unique('users', 'phone')->ignore($user->id), // Ignore unique check for current user
            ],
            'flag' => 'required',
            'Civil_number' => [
                'required',
                'max:12',
                ValidationRule::unique('users', 'Civil_number')->ignore($user->id),
            ],
            'file_number' => [
                'required',
                ValidationRule::unique('users', 'file_number')->ignore($user->id),
            ],
            'grade_id' => 'required',
        ];

        // Add conditional email validation if email is present
        if ($request->filled('email')) {
            $rules['email'] = [
                'email',
                ValidationRule::unique('users', 'email')->ignore($user->id),
            ];
        }

        // Apply validation
        $validatedData = Validator::make($request->all(), $rules, $messages);
        // dd($request->all());
        // Handle validation failure
        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }

        // dd($request);
        if ($request->filled('department_id')) {
            $id_department = Departements::where('uuid', $request->department_id)->value('id');
        } else {
            $id_department =  null;
        }
        // Additional checks for 'user' flag
        if ($request->flag == 'user') {
            if (!$request->filled('email')) {
                return redirect()->back()->withErrors(['email' => 'البريد الإلكتروني مطلوب ولا يمكن تركه فارغاً.'])->withInput();
            }
            if (!$request->filled('rule_id')) {
                return redirect()->back()->withErrors(['rule_id' => 'المهام مطلوبة ولا يمكن تركها فارغة.'])->withInput();
            }
            if (empty($request->filled('password')) && !$user->password) {
                return redirect()->back()->withErrors(['password' => 'كلمة المرور مطلوبة ولا يمكن تركها فارغة.'])->withInput();
            }
        }
        ///dd($request->all());
        $old_sector = $user->sector;
        $old_department = $user->department_id;
        $old_flag = $user->flag;

        //if flag user and user sector manager , changed rule to dep manager
        // $is_sectormanager = Sector::where('manager', $user->id)->exists();
        // if ($request->flag == 'user' && $is_sectormanager && $request->rule_id == 3) {
        //     return redirect()->back()->withErrors(['rule_id' => 'لا يمكن تعديل هذا المستخدم ,لأنه مدير قطاع'])->withInput();
        // }
        // if ($request->sector != $old_sector || $old_flag != $request->flag) {
        //     $checksectormanger = Sector::where('manager', $user->id)->first();
        //     if ($checksectormanger) {
        //         $checksectormanger->manager = null;
        //         $checksectormanger->save();
        //     }
        //     if ($request->rule_id == 4) {
        //         $newsectormanger = Sector::find($request->sector);
        //         $newsectormanger->manager = $user->id;
        //         $newsectormanger->save();
        //         if ($user->email && isValidEmail($user->email)) {

        //             Sendmail('مدير قطاع', ' تم أضافتك كمدير قطاع' . $request->name, $user->Civil_number, 123456, $user->email);
        //         }
        //     }
        // }

        // if ($id_department != $old_department || $old_flag != $request->flag) {

        //     $checkdepmanger = departements::where('manger', $user->id)->first();
        //     if ($checkdepmanger) {
        //         $checkdepmanger->manger = null;
        //         $checkdepmanger->save();
        //     }
        //     if ($request->rule_id == 3) {
        //         // dd($request->department_id, $id_department);
        //         $newdepmanger = departements::find($id_department);
        //         if ($newdepmanger) {
        //             $old_user = User::find($newdepmanger->manger);
        //             if ($old_user) {

        //                 $old_user->flag = 'employee';
        //                 $old_user->password = null;
        //                 $old_user->rule_id = null;
        //                 $old_user->save();
        //             }
        //         }
        //         $newdepmanger->manger = $user->id;
        //         $newdepmanger->save();
        //         if ($user->email && isValidEmail($user->email)) {

        //             Sendmail('مدير أداره', ' تم أضافتك كمدير أداره' . $request->name, $user->Civil_number, 123456, $user->email);
        //         }
        //     }
        // }

        // Update user attributes
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->description = $request->description;
        $user->military_number = $military_number;
        $user->job_title = $request->job_title;
        $user->job_id = $request->job_id;
        $user->nationality = $request->nationality;
        $user->Civil_number = $request->Civil_number;
        $user->file_number = $request->file_number;
        $user->flag = $request->flag;
        $user->seniority = $request->seniority;
        $user->Provinces = $request->Provinces;
        $user->sector = $request->sector;
        $user->region = $request->region;
        $user->public_administration = $id_department;
        $user->department_id = $id_department;
        $user->work_location = $request->work_location;
        $user->qualification = $request->qualification;
        $user->date_of_birth = $request->date_of_birth;
        $user->joining_date = $request->joining_date;
        $user->type = $request->gender;

        // Calculate age and service length
        $user->age = Carbon::parse($request->input('date_of_birth'))->age;
        $user->length_of_service = $request->input('end_of_service');

        // Update grade if present
        if ($request->has('grade_id')) {
            $user->grade_id = $request->grade_id;
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $file = $request->image;
            $path = 'users/user_profile';
            UploadFilesWithoutReal($path, 'image', $user, $file);
        }

        // Set rule_id and password if flag is 'user'
        if ($request->flag == 'user') {
            $user->rule_id = $request->rule_id;
            if ($request->password != null) {
                $user->password =  Hash::make($request->password);
            }
        }
        if ($request->flag == 'employee') {
            $user->rule_id = null;
            $user->password =  null;
        }
        // Save user data
        $user->save();
        // dd($request->id_department ,$id_department , $old_department ,$old_flag , $request->flag );

        // $department = departements::where('manger', $user->id)->where('id', '<>', $request->public_administration)->first();
        // if ($department) {
        //     $department->manger = null;
        //     $department->save();
        // }
        // $sector = sector::where('manager', $user->id)->where('id', '<>', $request->sector)->first();
        // if ($sector) {
        //     $sector->manager = null;
        //     $sector->save();
        // }
        UpdateUserHistory($user->id);
        addUserHistory($user->id,  $id_department,  $request->sector);
        UpdateUserGradeHistory($user->id);
        addUserGradeHistory($user->id, $user->grade_id);
        session()->flash('success', 'تم الحفظ بنجاح.');
        return redirect()->route('user.employees', $request->flag);
    }

    public function getGoverment($id)
    {
        $sector = Sector::find($id);

        $governments = Government::whereIn('id', $sector->governments_IDs)->get();

        return response()->json($governments);
    }

    public function getRegion($id)
    {

        $area = Region::where('government_id', $id)->get();
        return response()->json($area);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function importView(Request $request)
    {
        return view('importFile');
    }

    public function import(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        try {
            // If no errors, proceed to import the data
            Excel::import(new ImportUser, $request->file('file'));

            return redirect()->back()->with('success', 'Users imported successfully!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = 'Error in row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            return redirect()->back()->withErrors(['errors' => $errorMessages]);
        }
    }


    public function exportUsers(Request $request)
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
    public function downloadTemplate()
    {
        return Excel::download(new UsersImportTemplate, 'users_import_template.xlsx');
    }
    public function printUsers(Request $request)
    {

        // Fetch the user by Civil_number
        $user = User::all();

        if ($user) {
            // Create query for ReservationAllowance based on user_id and optional date range


            // Create a new TCPDF instance
            $pdf = new TCPDF();

            // Set document information
            $pdf->SetCreator('Your App');
            $pdf->SetAuthor('Your App');
            $pdf->SetTitle('Reservation Report');
            $pdf->SetSubject('Report');

            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // Set margins
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);

            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 10);

            // Set font for Arabic
            $pdf->SetFont('dejavusans', '', 12);

            // Add a page
            $pdf->AddPage();

            // Set RTL direction
            $pdf->setRTL(true);

            // Write HTML content
            $html = view('user.view', [
                'user' => $user,
            ])->render();

            // Print text using writeHTMLCell method
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

            // Output PDF
            return $pdf->Output('users.pdf', 'I'); // 'I' will display in the browser
        } else {
            return redirect()->back()->with('error', 'No user found with this Civil Number');
        }
    }
}
