<?php
namespace App\Imports;

use App\Models\Country;
use App\Models\Departements;
use App\Models\Rule;
use App\Models\User;
use App\Models\ViolationTypes;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter as ImportsHeadingRowFormatter;

class ImportUser implements ToModel, WithValidation, WithHeadingRow
{
    public function __construct()
    {
        ImportsHeadingRowFormatter::default('none');
    }

    public function model(array $row)
    {
        Log::info('Row data being processed:', $row); // Log the data for inspection

        // Check if the user already exists by email
        if (User::where('email', $row['البريد الالكتروني'])->exists()) {
            return null; // Skip duplicate rows
        }

        // Convert Arabic values in `flag` back to English equivalents
        $flag = $row['النوع'];
        if ($flag == 'مستخدم') {
            $flag = 'user';
        } elseif ($flag == 'موظف') {
            $flag = 'employee';
        }

        // Convert Arabic values in `type_military` back to ID values if provided
        $type_military = !empty($row['نوع العسكري']) ? ViolationTypes::where('name', $row['نوع العسكري'])->first()->id ?? null : null;

        // Get department ID from name
        $department_id = !empty($row['الادارة']) ? Departements::where('name', $row['الادارة'])->first()->id ?? null : null;

        // Get rule ID from name
        $rule_id = !empty($row['المهام']) ? Rule::where('name', $row['المهام'])->first()->id ?? null : null;

        // Get country ID from name
        $country_id = !empty($row['الدولة']) ? Country::where('country_name_ar', $row['الدولة'])->first()->id ?? null : null;

        // Create a new user if no duplicates were found
        return new User([
            'name' => $row['الاسم'],
            'email' => $row['البريد الالكتروني'],
            'phone' => $row['رقم الهاتف'],
            'Civil_number' => $row['رقم المدني'],
            'file_number' => $row['رقم الملف'],
            'flag' => $flag,
            'type_military' => $type_military,
            'password' => bcrypt($row['كلمة المرور']),
            'department_id' => $department_id,
            'rule_id' => $rule_id,
            'nationality' => $country_id,
        ]);
    }

    public function rules(): array
    {
        // Get allowed values from the database
        $allowedDepartments = Departements::pluck('name')->toArray();
        $allowedRules = Rule::pluck('name')->toArray(); // Ensure correct retrieval of allowed rules
        $allowedCountries = Country::pluck('country_name_ar')->toArray();
        $allowedFlag = ['موظف', 'مستخدم'];

        return [
            'الاسم' => 'required|string',
            'البريد الالكتروني' => 'required|email|unique:users,email',
            'رقم الهاتف' => 'required|string',
            'رقم المدني' => 'required|string',
            'رقم الملف' => 'required|string',
            'النوع' => 'required|string|in:' . implode(',', $allowedFlag),
            'نوع العسكري' => 'nullable|string',
            'كلمة المرور' => 'required|min:6', // Ensure minimum length is met
            'الادارة' => 'nullable|in:' . implode(',', $allowedDepartments),
            'المهام' => 'nullable|in:' . implode(',', $allowedRules),
            'الدولة' => 'nullable|in:' . implode(',', $allowedCountries),
        ];
    }

    public function customValidationMessages()
    {
        return [
            'الاسم.required' => 'حقل الاسم مطلوب.',
            'الاسم.string' => 'حقل الاسم يجب أن يكون نصًا.',
            'البريد الالكتروني.required' => 'البريد الإلكتروني مطلوب.',
            'البريد الالكتروني.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'البريد الالكتروني.unique' => 'البريد الإلكتروني :value موجود مسبقًا.',
            'رقم الهاتف.required' => 'رقم الهاتف مطلوب.',
            'رقم الهاتف.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'رقم المدني.required' => 'رقم المدني مطلوب.',
            'رقم المدني.string' => 'رقم المدني يجب أن يكون نصًا.',
            'رقم الملف.required' => 'رقم الملف مطلوب.',
            'رقم الملف.string' => 'رقم الملف يجب أن يكون نصًا.',
            'كلمة المرور.required' => 'كلمة المرور مطلوبة.',
            'كلمة المرور.string' => 'كلمة المرور يجب أن تكون نصًا.',
            'كلمة المرور.min' => 'يجب أن تحتوي كلمة المرور على 6 أحرف على الأقل.',
            'النوع.in' => 'النوع يجب أن يكون "user" أو "employee".',
            'نوع العسكري.exists' => 'نوع العسكري غير موجود.',
            'نوع العسكري.string' => 'نوع العسكري يجب أن يكون نصًا.',
            'الادارة.in' => 'الإدارة المحددة غير صحيحة.',
            'المهام.in' => 'المهام المحددة غير صحيحة.',
            'الدولة.in' => 'الدولة المحددة غير صحيحة.',
        ];
    }
}
