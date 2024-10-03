<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with(['department', 'rule']) // Eager load related models
            ->select('name', 'email', 'phone', 'military_number', 'flag', 'type_military', 'employee_type', 'password', 'rule_id', 'department_id') // Include foreign keys
            ->get()
            ->map(function ($user) {
                // Check and replace the `flag` value with Arabic equivalents
                if ($user->flag == 'user') {
                    $user->flag = 'مستخدم';
                } elseif ($user->flag == 'employee') {
                    $user->flag = 'موظف';
                }

                // Modify the `type_military` value with Arabic equivalents
                if ($user->type_military == 'police') {
                    $user->type_military = 'ظابط';
                } elseif ($user->type_military == 'police_') {
                    $user->type_military = 'صف ظابط';
                }

                // Modify the `employee_type` with Arabic equivalents
                if ($user->employee_type == 'civil') {
                    $user->employee_type  = 'مدني';
                } elseif ($user->employee_type == 'military') {
                    $user->employee_type  = 'عسكري';
                }

                // Append related department and role names
                // Change department_id to department_name
                $user->department_name = $user->department ? $user->department->name : null; // Use a new variable for department name

                // Change rule_id to rule_name
                $user->rule_name = $user->rule ? $user->rule->name : null; // Use a new variable for role name

                return $user;
            })
            ->map(function ($user) {
                // Create a new array with the required fields for the export
                return [
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->military_number,
                    $user->flag,
                    $user->type_military,
                    $user->employee_type,
                    $user->password, // Keep password, if you want it in the export
                    $user->department_name, // Add the department name to the export
                    $user->rule_name // Add the role name to the export
                ];
            });
    }

    /**
     * Set the headings for the Excel sheet.
     */
    public function headings(): array
    {
        return [
            'الاسم',
            'البريد الالكتروني',
            'رقم الهاتف',
            'رقم العسكري',
            'النوع',
            'نوع العسكري',
            'نوع الموظف',
            'كلمة المرور',
            'الادارة',
            'المهام'
        ];
    }
}
